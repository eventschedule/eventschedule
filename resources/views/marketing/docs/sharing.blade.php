<x-marketing-layout>
    <x-slot name="title">Sharing Your Schedule - Event Schedule</x-slot>
    <x-slot name="description">Learn how to share your Event Schedule with the world. Embed on your website, share on social media, and grow your audience.</x-slot>
    <x-slot name="keywords">share schedule, embed calendar, widget, social media sharing, iCal feed, RSS feed</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-16 overflow-hidden border-b border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-violet-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-purple-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Sharing Your Schedule" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-500/20">
                    <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-white">Sharing Your Schedule</h1>
            </div>
            <p class="text-lg text-gray-400 max-w-3xl">
                Share your schedule with the world. Embed it on your website, share on social media, and let fans follow your events.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <a href="#schedule-url" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Your Schedule URL</a>
                        <a href="#embed" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Embedding on Your Website</a>
                        <a href="#social" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Social Media Sharing</a>
                        <a href="#followers" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Building Followers</a>
                        <a href="#feeds" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">iCal and RSS Feeds</a>
                        <a href="#qr-code" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">QR Codes</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Schedule URL -->
                        <section id="schedule-url" class="doc-section">
                            <h2 class="doc-heading">Your Schedule URL</h2>
                            <p class="text-gray-300 mb-6">Every schedule gets a unique, shareable URL. This is the primary way people will find and view your events.</p>

                            <div class="bg-white/5 rounded-xl p-4 border border-white/10 mb-6">
                                <p class="text-sm text-gray-400 mb-2">Your schedule URL format:</p>
                                <code class="text-violet-400">{{ config('app.url') }}/your-schedule-name</code>
                            </div>

                            <p class="text-gray-300 mb-4">Share this link anywhere:</p>
                            <ul class="doc-list">
                                <li>Your website or bio</li>
                                <li>Social media profiles</li>
                                <li>Email signatures</li>
                                <li>Printed materials</li>
                            </ul>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Pro Feature: Custom Domain</div>
                                <p>With a Pro plan, you can use your own domain (e.g., <code class="doc-inline-code">events.yourdomain.com</code>) for a more professional look.</p>
                            </div>
                        </section>

                        <!-- Embedding -->
                        <section id="embed" class="doc-section">
                            <h2 class="doc-heading">Embedding on Your Website</h2>
                            <p class="text-gray-300 mb-6">Add your schedule directly to your website using an embed code. Your events will automatically update without any extra work.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">Getting the Embed Code</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to your schedule's public page</li>
                                <li>Click the <strong class="text-white">"Embed"</strong> button (or look for the embed icon)</li>
                                <li>Copy the HTML code provided</li>
                                <li>Paste it into your website where you want the schedule to appear</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">Embed Options</h3>
                            <p class="text-gray-300 mb-4">Customize how your embedded schedule looks:</p>

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
                                            <td><span class="font-semibold text-white">Height</span></td>
                                            <td>Set the height of the embed (in pixels or percentage)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-white">Theme</span></td>
                                            <td>Light or dark mode to match your website</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-white">Header</span></td>
                                            <td>Show or hide the schedule header</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Social Media -->
                        <section id="social" class="doc-section">
                            <h2 class="doc-heading">Social Media Sharing</h2>
                            <p class="text-gray-300 mb-6">Share your schedule and individual events on social media to reach more people.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">Sharing Your Schedule</h3>
                            <p class="text-gray-300 mb-4">Simply share your schedule URL on any platform. Event Schedule automatically generates social media preview cards with your schedule name, description, and image.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">Sharing Individual Events</h3>
                            <p class="text-gray-300 mb-4">Each event also has its own URL that you can share. The preview will show the event name, date, and flyer image.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">Event Graphics</h3>
                            <p class="text-gray-300 mb-4">Use the <a href="{{ route('marketing.docs.event_graphics') }}" class="text-violet-400 hover:text-violet-300">Event Graphics</a> feature to generate shareable images showing multiple upcoming events. Perfect for weekly social media posts.</p>
                        </section>

                        <!-- Followers -->
                        <section id="followers" class="doc-section">
                            <h2 class="doc-heading">Building Followers</h2>
                            <p class="text-gray-300 mb-6">Let fans follow your schedule to get notified about new events. This builds your audience over time.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">How Following Works</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Visitors click <strong class="text-white">"Follow"</strong> on your schedule</li>
                                <li>They enter their email address</li>
                                <li>They receive email notifications when you add new events</li>
                                <li>They can unsubscribe at any time</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">Managing Followers</h3>
                            <p class="text-gray-300 mb-4">View and manage your followers from the <strong class="text-white">Followers</strong> tab in your schedule admin. You can:</p>
                            <ul class="doc-list">
                                <li>See how many followers you have</li>
                                <li>View follower growth over time</li>
                                <li>Export your follower list</li>
                            </ul>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Privacy</div>
                                <p>Follower emails are only used for event notifications. We never share or sell email addresses.</p>
                            </div>
                        </section>

                        <!-- Feeds -->
                        <section id="feeds" class="doc-section">
                            <h2 class="doc-heading">iCal and RSS Feeds</h2>
                            <p class="text-gray-300 mb-6">Your schedule automatically provides standard feeds that work with calendar apps and feed readers.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">iCal Feed</h3>
                            <p class="text-gray-300 mb-4">Subscribe to your schedule in any calendar app (Google Calendar, Apple Calendar, Outlook, etc.) using the iCal feed URL.</p>
                            <div class="bg-white/5 rounded-xl p-4 border border-white/10 mb-6">
                                <code class="text-violet-400 text-sm">{{ config('app.url') }}/your-schedule-name/ical</code>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4">RSS Feed</h3>
                            <p class="text-gray-300 mb-4">Subscribe to your schedule in any RSS reader to get updates when new events are added.</p>
                            <div class="bg-white/5 rounded-xl p-4 border border-white/10 mb-6">
                                <code class="text-violet-400 text-sm">{{ config('app.url') }}/your-schedule-name/rss</code>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4">JSON Feed</h3>
                            <p class="text-gray-300 mb-4">For developers, a JSON feed is also available for programmatic access.</p>
                            <div class="bg-white/5 rounded-xl p-4 border border-white/10 mb-6">
                                <code class="text-violet-400 text-sm">{{ config('app.url') }}/your-schedule-name/json</code>
                            </div>
                        </section>

                        <!-- QR Codes -->
                        <section id="qr-code" class="doc-section">
                            <h2 class="doc-heading">QR Codes</h2>
                            <p class="text-gray-300 mb-6">Generate a QR code for your schedule to use in printed materials, posters, or at your venue.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to your schedule's <strong class="text-white">Followers</strong> tab</li>
                                <li>Click <strong class="text-white">"QR Code"</strong></li>
                                <li>Download the QR code image</li>
                                <li>Use it on flyers, posters, table tents, or anywhere else</li>
                            </ol>

                            <p class="text-gray-300">When scanned, the QR code takes people directly to your schedule where they can view events and follow you.</p>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
