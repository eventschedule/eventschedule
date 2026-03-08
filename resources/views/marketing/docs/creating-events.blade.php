<x-marketing-layout>
    <x-slot name="title">Creating Events - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Creating Events</x-slot>
    <x-slot name="description">Learn how to add events to your schedule and configure event settings like venue, participants, tickets, and more.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Creating Events - Event Schedule",
        "description": "Learn how to add events to your schedule and configure event settings like venue, participants, tickets, and more.",
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
        "dateModified": "2026-03-08"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Creating Events" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Creating Events</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Add events to your schedule and configure event settings like venue, participants, tickets, and more.
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
                        <a href="#manual" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Creating Events Manually</a>
                        <a href="#details" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Details</a>
                        <a href="#ai-details-generator" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">AI Details Generator</a>
                        <a href="#venue" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Venue</a>
                        <a href="#participants" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Participants</a>
                        <a href="#recurring" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Recurring</a>
                        <a href="#agenda" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Agenda</a>
                        <a href="#schedules" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Schedules</a>
                        <a href="#google-calendar" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Google Calendar</a>
                        <a href="#whatsapp" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">WhatsApp</a>
                        <a href="#tickets" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Tickets</a>
                        <div class="doc-nav-group">
                            <a href="#event-settings" class="doc-nav-group-header doc-nav-link">Event Settings <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#privacy" class="doc-nav-link">Privacy</a>
                                <a href="#custom-fields" class="doc-nav-link">Custom Fields</a>
                            </div>
                        </div>
                        <div class="doc-nav-group">
                            <a href="#engagement" class="doc-nav-group-header doc-nav-link">Engagement <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#fan-content" class="doc-nav-link">Fan Content</a>
                                <a href="#polls" class="doc-nav-link">Polls</a>
                                <a href="#feedback" class="doc-nav-link">Feedback</a>
                            </div>
                        </div>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Manual Creation -->
                        <section id="manual" class="doc-section">
                            <h2 class="doc-heading">Creating Events Manually</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The simplest way to add an event is to create it manually from your schedule's admin page.</p>

                            <x-doc-screenshot id="creating-events--schedule-tab" alt="Schedule event list view" loading="eager" />

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule</strong></li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Add Event"</strong></li>
                                <li>Fill in the event details:
                                    <ul class="doc-list mt-2 mb-2">
                                        <li>Event name (required)</li>
                                        <li>Date and time</li>
                                        <li>Duration</li>
                                        <li>Venue/location</li>
                                        <li>Description</li>
                                        <li>Event image</li>
                                    </ul>
                                </li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Save"</strong> to publish the event</li>
                            </ol>

                            <x-doc-screenshot id="creating-events--add-event" alt="Add event form" />

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Import with AI</div>
                                <p>Don't want to type everything manually? You can also <a href="{{ route('marketing.docs.ai_import') }}" class="text-cyan-400 hover:text-cyan-300">import events using AI</a> from text or images.</p>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Pending Events</div>
                                <p>If you have <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-requests" class="text-cyan-400 hover:text-cyan-300">Require Approval</a> enabled, submitted events appear in a pending queue. Review them in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Pending</strong> and approve or reject each one.</p>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Details tab contains the core information for your event:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Field</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Name</span></td>
                                            <td>The event title (required)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Category</span></td>
                                            <td>Select an event category from the dropdown (e.g. Concert, Workshop, Conference)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Date, Start Time & End Time</span></td>
                                            <td>The event date, start time, and end time. Duration is calculated automatically from the start and end times.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Short Description</span></td>
                                            <td>A brief summary of the event (up to 200 characters). Appears as a subtitle on the event page.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Description</span></td>
                                            <td>Details about the event (supports markdown formatting)</td>
                                        </tr>
                                        <tr>
                                            <td id="ai-flyer"><span class="font-semibold text-gray-900 dark:text-white">Image</span></td>
                                            <td>A flyer or photo for the event. Enterprise users can also click <strong>Generate AI Flyer</strong> to automatically create a professional flyer from the event details using AI. You can optionally describe your preferred style (e.g. "minimalist, blue and white") before generating.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Sub-schedule</span></td>
                                            <td>Organize events by type (e.g., "Live Music", "Comedy"). See <a href="{{ route('marketing.docs.creating_schedules') }}#customize-subschedules" class="text-cyan-400 hover:text-cyan-300">Sub-schedules</a></td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Event URL / Slug</span></td>
                                            <td>For existing events, the event URL is displayed with a copy button. You can click <strong>Edit</strong> to customize the URL slug.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- AI Details Generator -->
                        <section id="ai-details-generator" class="doc-section">
                            <h2 class="doc-heading">AI Details Generator <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 ml-2">Enterprise</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Let AI generate a sub-schedule, short description, and description for your event based on its name and your schedule's context.</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">How It Works</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Click the <strong class="text-gray-900 dark:text-white">AI</strong> button in the Details tab header, select which fields to generate, and the AI will create content based on your event's name and your schedule's context.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Generated Fields</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Sub-schedule</strong>, <strong class="text-gray-900 dark:text-white">short description</strong>, and <strong class="text-gray-900 dark:text-white">description</strong>. Fields with existing values show a blue dot indicator; generating will replace their content.</p>
                                </div>
                            </div>
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-6">
                                <p class="text-sm text-blue-800 dark:text-blue-300"><strong>Note:</strong> Selfhosted installations require a <x-link href="https://ai.google.dev/" target="_blank">Gemini API key</x-link> configured in the environment settings for AI features to work.</p>
                            </div>
                        </section>

                        <!-- Venue -->
                        <section id="venue" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-1.5-1.5v18m7.5-18v18" />
                                </svg>
                                Venue
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Venue tab lets you specify where your event takes place. Events can be in-person, online, or both.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">In-Person Events</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose <strong class="text-gray-900 dark:text-white">Use Existing</strong> to select a venue that has appeared on your schedule before, or <strong class="text-gray-900 dark:text-white">Create New</strong> to enter a new venue name and address. An interactive map is displayed on the public event page.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Address Validation</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">After entering an address, click <strong class="text-gray-900 dark:text-white">Validate Address</strong> to verify the location and generate map coordinates. Use the <strong class="text-gray-900 dark:text-white">View Map</strong> button to preview the location on a map before saving.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Online Events</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Toggle the online event option and provide a URL (e.g., a Zoom or Google Meet link). The link is displayed on the event page for guests to join.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Venue Contact</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Optionally add the venue's email and website so guests can contact the venue directly. On the hosted platform, you can check <strong class="text-gray-900 dark:text-white">"Send email to notify"</strong> to send the venue an email about the event.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Participants -->
                        <section id="participants" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                Participants
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Participants tab lets you tag performers, speakers, or other participants on an event. Participants appear on the public event page with links to their profiles.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Adding Participants</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Search for existing members on your schedule or add new ones. Each participant is linked to their schedule profile page. You can also provide an <strong class="text-gray-900 dark:text-white">email address</strong> and a <strong class="text-gray-900 dark:text-white">YouTube video URL</strong> for each participant.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Notify Participants</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">On the hosted platform, you can check <strong class="text-gray-900 dark:text-white">"Send email to notify"</strong> for each participant to send them an email about the event.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">When to Use</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Participants are most useful for Talent and Curator-type schedules where events feature specific performers, speakers, or artists. For Venue-type schedules, this tab is optional.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Recurring Events -->
                        <section id="recurring" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                Recurring
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Recurring tab lets you create events that repeat on a regular schedule. Instead of manually adding each date, you set a recurrence pattern and Event Schedule generates all the individual event dates for you.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">To add multiple dates, click <strong class="text-gray-900 dark:text-white">"Add Dates"</strong> on the Recurring tab and choose a frequency pattern. You can also add individual dates manually.</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Frequency</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Daily</span></td>
                                            <td>Repeats every day</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Weekly</span></td>
                                            <td>Repeats every week. Select which days of the week the event occurs on using the day checkboxes (Sun, Mon, Tue, etc.).</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Every N weeks</span></td>
                                            <td>Repeats every 2, 3, or more weeks (e.g., biweekly). Includes day-of-week checkboxes to select which days.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Monthly (same date)</span></td>
                                            <td>Repeats on the same date each month (e.g., the 15th)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Monthly (same day)</span></td>
                                            <td>Repeats on the same weekday each month (e.g., the second Tuesday)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Yearly</span></td>
                                            <td>Repeats once a year on the same date</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">Each recurrence pattern has an end condition that controls when the series stops:</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Never</strong> - The event repeats indefinitely until you stop it manually.</li>
                                <li><strong class="text-gray-900 dark:text-white">On a specific date</strong> - The event repeats until the given end date.</li>
                                <li><strong class="text-gray-900 dark:text-white">After N occurrences</strong> - The event repeats a fixed number of times.</li>
                            </ul>

                            <p class="text-gray-600 dark:text-gray-300 mb-6">You can also fine-tune the generated dates by adding <strong class="text-gray-900 dark:text-white">date exceptions</strong>. Exclude specific dates when the event will not take place (e.g., holidays), or include extra dates that fall outside the regular pattern.</p>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Google Calendar</div>
                                <p>If you have <a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="text-cyan-400 hover:text-cyan-300">Google Calendar sync</a> enabled, recurring events are synced as individual occurrences so each date appears separately in both calendars.</p>
                            </div>
                        </section>

                        <!-- Agenda -->
                        <section id="agenda" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                Agenda
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Agenda tab lets you break an event into individual parts or segments, such as performances, sessions, or talks. Each part appears on the public event page so guests can see what to expect.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Adding Parts</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Click <strong class="text-gray-900 dark:text-white">"Add Part"</strong> on the Agenda tab and fill in the details:</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Name</strong> (required) - The title of the part (e.g., "Opening Keynote", "DJ Set")</li>
                                <li><strong class="text-gray-900 dark:text-white">Start & End Time</strong> (optional) - When this part takes place within the event</li>
                                <li><strong class="text-gray-900 dark:text-white">Description</strong> (optional) - Additional details about this part</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reordering</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Rearrange parts using drag-and-drop or the up/down buttons to control the order they appear on the event page.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Display Options</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">You can choose whether to show or hide times and descriptions for event parts on the public event page.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">AI Import</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">You can import event parts using AI in two ways:</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Import from Image</strong> - Upload a photo of a printed agenda or schedule and AI will extract each part automatically.</li>
                                <li><strong class="text-gray-900 dark:text-white">Import from Text</strong> - Paste agenda text (e.g., a lineup list) and AI will parse it into individual parts.</li>
                            </ul>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Additional AI options:</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">AI Agenda Prompt</strong> - Add custom instructions to help AI interpret your specific agenda format (up to 500 characters).</li>
                                <li><strong class="text-gray-900 dark:text-white">Save Agenda Image</strong> - Save the uploaded agenda image as the event's featured image.</li>
                                <li><strong class="text-gray-900 dark:text-white">Save as Default</strong> - Save the AI prompt as the default for all future events on this schedule.</li>
                            </ul>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>AI agenda import requires a <a href="{{ route('marketing.docs.ai_import') }}" class="text-cyan-400 hover:text-cyan-300">Gemini API key</a> to be configured.</p>
                            </div>
                        </section>

                        <!-- Schedules -->
                        <section id="schedules" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                Schedules
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Schedules tab lets you share events to other Curator-type schedules you manage. This is useful when you want an event to appear on multiple schedules at once.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-6">For each curator schedule you select, you can also choose which <strong class="text-gray-900 dark:text-white">sub-schedule</strong> the event should be assigned to on that curator's schedule (if the curator has sub-schedules configured).</p>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Conditional Tab</div>
                                <p>This tab only appears when you manage multiple Curator-type schedules. If you only have one schedule, you will not see this tab.</p>
                            </div>
                        </section>

                        <!-- Google Calendar -->
                        <section id="google-calendar" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                Google Calendar
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Google Calendar tab lets you sync individual events to or from Google Calendar. When calendar sync is enabled, events created in either place will appear in both.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">To use this tab, you must first set up calendar sync in your schedule settings. See <a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="text-cyan-400 hover:text-cyan-300">Calendar Integrations</a> for setup instructions.</p>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Recurring events sync to Google Calendar as individual occurrences, so each date appears separately in both calendars.</p>
                            </div>
                        </section>

                        <!-- WhatsApp -->
                        <section id="whatsapp" class="doc-section">
                            <h2 class="doc-heading">Creating Events via WhatsApp <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 ml-2">Enterprise</span></h2>

                            <div class="doc-callout doc-callout-info mb-6">
                                <div class="doc-callout-title">Enterprise Feature</div>
                                <p>Creating events via WhatsApp is an Enterprise feature. It requires Twilio integration.</p>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-6">Send a WhatsApp message to create events directly from your phone. You can send event details as text, or send a photo of a flyer or poster and AI will extract the information automatically.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">How It Works</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Verify your phone number in <strong class="text-gray-900 dark:text-white">Account Settings</strong></li>
                                <li>Send a WhatsApp message to the Event Schedule number</li>
                                <li>Include event details as text, or send a photo of a flyer or poster</li>
                                <li>AI parses the details and creates the event on your default schedule</li>
                                <li>You receive a confirmation message with the event name, date, and link</li>
                            </ol>

                            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">What AI Extracts</h4>
                                <ul class="doc-list text-sm">
                                    <li>Event name</li>
                                    <li>Date and time</li>
                                    <li>Duration</li>
                                    <li>Venue (name, address, city, state)</li>
                                    <li>Description</li>
                                    <li>Flyer image</li>
                                    <li>Category</li>
                                </ul>
                            </div>

                            <div class="doc-callout doc-callout-tip mb-6">
                                <div class="doc-callout-title">Tip</div>
                                <p>Works great with event flyers - snap a photo and send it via WhatsApp to create your event in seconds.</p>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">SaaS Deployment</div>
                                <p>WhatsApp event creation requires Twilio to be configured. See the <a href="{{ route('marketing.docs.saas.twilio') }}" class="text-cyan-400 hover:text-cyan-300">Twilio setup guide</a> for configuration details.</p>
                            </div>
                        </section>

                        <!-- Tickets -->
                        <section id="tickets" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                </svg>
                                Tickets <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 ml-2">Pro</span>
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Sell tickets directly from your event pages with built-in Stripe payments. Set up ticket types, manage sales, and check in attendees at the door. See the full <a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Selling Tickets</a> guide for setup, sales management, and check-in details.
                            </p>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">
                                If you are not using built-in ticketing or RSVP, the Tickets section shows fields for <strong class="text-gray-900 dark:text-white">external ticketing</strong>: a <strong class="text-gray-900 dark:text-white">Registration URL</strong> (link to your external ticketing page), <strong class="text-gray-900 dark:text-white">Ticket Price</strong> (with currency selector, used in <a href="{{ route('marketing.docs.event_graphics') }}#text-templates" class="text-cyan-400 hover:text-cyan-300">event graphics text templates</a>), and an optional <strong class="text-gray-900 dark:text-white">Coupon Code</strong>.
                            </p>
                        </section>

                        <!-- Event Settings -->
                        <section id="event-settings" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                Event Settings
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Configure event-level privacy and custom data fields.</p>
                        </section>

                        <!-- Privacy -->
                        <section id="privacy" class="doc-section">
                            <h2 class="doc-heading">Privacy <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 ml-2">Enterprise</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Control event visibility with per-event privacy settings. Private events are hidden from your public schedule and require a password to view.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Making an Event Private</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>When creating or editing an event, toggle the <strong class="text-gray-900 dark:text-white">"Private"</strong> option</li>
                                <li>Set a password for the event</li>
                                <li>Save the event</li>
                            </ol>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">Private events are hidden from your public schedule page and calendar views. Visitors can only access them via a direct link and must enter the correct password to view the event details.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Mix Public and Private</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Privacy is set per event, not per schedule. You can freely mix public and private events on the same schedule. Public events appear normally while private events remain hidden.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sharing Private Events</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Share the event's direct link and password with your intended audience via email, messaging, or any other channel. Only people with both the link and the correct password can view the event.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>For schedule-level settings, see <a href="{{ route('marketing.docs.creating_schedules') }}#settings-advanced" class="text-cyan-400 hover:text-cyan-300">Advanced Settings</a>.</p>
                            </div>
                        </section>

                        <!-- Custom Fields -->
                        <section id="custom-fields" class="doc-section">
                            <h2 class="doc-heading">Custom Fields <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 ml-2">Pro</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Add custom data fields to your events to capture additional information beyond the standard fields.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Setting Up Custom Fields</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong></li>
                                <li>Scroll to <strong class="text-gray-900 dark:text-white">Custom Fields</strong></li>
                                <li>Add fields with a name and type (string, multiline string, switch, date, dropdown, or multiselect)</li>
                                <li>Save your settings</li>
                            </ol>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">Once configured, custom fields appear on the event creation and edit forms. You can define up to 10 custom fields per schedule.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Use Cases</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Track performer names, room numbers, age restrictions, dress codes, or any event-specific data. Custom field values are available as variables in <a href="{{ route('marketing.docs.event_graphics') }}#variables" class="text-cyan-400 hover:text-cyan-300">event graphics text templates</a>.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Field Types</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">String:</strong> Single-line text input. <strong class="text-gray-900 dark:text-white">Multiline String:</strong> Multi-line text area. <strong class="text-gray-900 dark:text-white">Switch:</strong> On/off toggle. <strong class="text-gray-900 dark:text-white">Date:</strong> Date picker. <strong class="text-gray-900 dark:text-white">Dropdown:</strong> Single select from predefined options. <strong class="text-gray-900 dark:text-white">Multiselect:</strong> Multiple selections from predefined options.</p>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Add interactive features to your events including fan content, polls, and post-event feedback.</p>
                        </section>

                        <!-- Fan Content -->
                        <section id="fan-content" class="doc-section">
                            <h2 class="doc-heading">Fan Content</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Fan Content lets your audience submit YouTube or Vimeo videos and text comments on event pages. This is available for Curator-type schedules and gives fans a way to share their experience while keeping you in control of what appears publicly.
                            </p>

                            <x-doc-screenshot id="fan-content--videos-tab" alt="Fan videos management tab" />

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                All fan submissions go through an approval workflow. Nothing appears on your public event page until you approve it.
                            </p>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>Fan Content is available for schedules with the Curator type. You can change your schedule type in the schedule settings.</p>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Enabling Fan Content</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to your schedule's admin panel</li>
                                <li>Open <strong>Settings</strong></li>
                                <li>Set the schedule type to <strong>Curator</strong></li>
                                <li>Save your changes</li>
                            </ol>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">
                                Once the schedule type is set to Curator, the <strong>Videos</strong> tab appears in your admin panel and fans can submit content on your public event pages.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Content Types</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Three types of fan content are available, each controlled by a separate toggle at the schedule level:
                            </p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Fan Comments</strong> - Text comments on events and event parts</li>
                                <li><strong class="text-gray-900 dark:text-white">Fan Photos</strong> - Photo uploads on events</li>
                                <li><strong class="text-gray-900 dark:text-white">Fan Videos</strong> - YouTube or Vimeo video links on events and event parts</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Per-Event Overrides</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                On the event edit page, each content type has a dropdown to override the schedule-level setting: <strong class="text-gray-900 dark:text-white">Use Schedule Default</strong>, <strong class="text-gray-900 dark:text-white">Enabled</strong>, or <strong class="text-gray-900 dark:text-white">Disabled</strong>. This lets you enable fan content for specific events while keeping it off schedule-wide, or vice versa.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Moderation</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                You have full control over which fan content appears on your event pages:
                            </p>
                            <ul class="doc-list mb-6">
                                <li><strong>Admin panel</strong> - Go to the Videos tab in your admin panel to see all pending submissions. You can approve or reject each one individually.</li>
                                <li><strong>Email notifications</strong> - When a fan submits content, you receive an email notification with links to approve or reject the submission directly.</li>
                            </ul>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Approved content appears on the public event page immediately. You can remove previously approved content at any time from the Videos tab in your admin panel.
                            </p>
                        </section>

                        <!-- Polls -->
                        <section id="polls" class="doc-section">
                            <h2 class="doc-heading">Polls <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 ml-2">Pro</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Add interactive multiple-choice questions to your events and let your guests vote on the options that matter most.
                            </p>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>Event Polls is a Pro feature. Upgrade your schedule to Pro to start creating polls on your events.</p>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Creating Polls</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to the event edit page in your admin panel</li>
                                <li>Scroll down to the <strong>Polls</strong> section</li>
                                <li>Enter your question</li>
                                <li>Add between 2 and 10 options for voters to choose from</li>
                                <li>Save the event</li>
                            </ol>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">
                                You can add up to 5 polls per event. Each poll has its own question and set of options.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">How Voting Works</h3>
                            <ul class="doc-list mb-6">
                                <li><strong>Sign in required</strong> - Guests must be signed in to vote, which ensures each person can only vote once.</li>
                                <li><strong>One click to vote</strong> - Guests simply click on the option they want to vote for.</li>
                                <li><strong>One vote per poll</strong> - Each guest can cast one vote per poll. Votes cannot be changed after submission.</li>
                                <li><strong>Instant results</strong> - After voting, results are shown immediately so guests can see how others voted.</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Viewing Results</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                After a guest casts their vote, poll results are displayed with progress bars showing the count and percentage for each option. The leading option is highlighted so it is easy to see which choice is ahead.
                            </p>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">
                                As an organizer, you can always see poll results in the event edit page of your admin panel, regardless of whether you have voted.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User-Suggested Options</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                You can allow guests to suggest their own poll options:
                            </p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Allow User Options</strong> - Enable this toggle to let guests add their own options to the poll.</li>
                                <li><strong class="text-gray-900 dark:text-white">Require Option Approval</strong> - When enabled (appears after enabling Allow User Options), user-suggested options go to a pending queue for your approval before appearing in the poll. Review pending options on the event edit page and approve or reject each one.</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Closing and Reopening</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                You can control whether a poll is accepting votes by toggling it between active and closed states:
                            </p>
                            <ul class="doc-list mb-6">
                                <li><strong>Active polls</strong> - Guests can vote and results update in real time.</li>
                                <li><strong>Closed polls</strong> - Results are still visible, but no new votes can be cast.</li>
                            </ul>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                You can reopen a closed poll at any time if you want to allow voting again. Toggle the poll status from the event edit page in your admin panel.
                            </p>
                        </section>

                        <!-- Feedback -->
                        <section id="feedback" class="doc-section">
                            <h2 class="doc-heading">Feedback <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 ml-2">Pro</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Control whether attendees receive a feedback request email after this event ends. By default, events inherit the schedule-level feedback setting.
                            </p>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>Post-event feedback is a Pro feature. Enable it at the schedule level in <strong>Settings &rarr; Notifications</strong>, then optionally override per event here.</p>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Per-Event Override</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The Feedback section on the event edit page offers three options:
                            </p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Use schedule default</strong> - Follows whatever you have set at the schedule level.</li>
                                <li><strong class="text-gray-900 dark:text-white">Enabled</strong> - Feedback emails will be sent for this event regardless of the schedule setting.</li>
                                <li><strong class="text-gray-900 dark:text-white">Disabled</strong> - No feedback emails will be sent for this event regardless of the schedule setting.</li>
                            </ul>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                For more details on viewing and managing feedback, see <a href="{{ route('marketing.docs.tickets') }}#feedback" class="text-cyan-400 hover:text-cyan-300">Selling Tickets &rarr; Post-Event Feedback</a>.
                            </p>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.ai_import') }}" class="text-cyan-400 hover:text-cyan-300">AI Import</a> - Import events from text or images using AI</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Selling Tickets</a> - Add tickets to your events</li>
                                <li><a href="{{ route('marketing.docs.event_graphics') }}" class="text-cyan-400 hover:text-cyan-300">Event Graphics</a> - Create promotional images</li>
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Sharing Your Schedule</a> - Share and embed your events</li>
                                <li><a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="text-cyan-400 hover:text-cyan-300">Calendar Integrations</a> - Set up Google Calendar and CalDAV sync</li>
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
        "name": "How to Create Events in Event Schedule",
        "description": "Learn how to add events to your schedule and configure event settings like venue, participants, tickets, and more.",
        "totalTime": "PT3M",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Access the Admin Panel",
                "text": "Go to Admin Panel and then Schedule to view your events.",
                "url": "{{ url(route('marketing.docs.creating_events')) }}#manual"
            },
            {
                "@type": "HowToStep",
                "name": "Click Add Event",
                "text": "Click the 'Add Event' button to open the event creation form.",
                "url": "{{ url(route('marketing.docs.creating_events')) }}#manual"
            },
            {
                "@type": "HowToStep",
                "name": "Fill in Event Details",
                "text": "Enter the event name, date and time, duration, venue/location, description, and upload an event image.",
                "url": "{{ url(route('marketing.docs.creating_events')) }}#details"
            },
            {
                "@type": "HowToStep",
                "name": "Save the Event",
                "text": "Click Save to publish the event to your schedule.",
                "url": "{{ url(route('marketing.docs.creating_events')) }}#manual"
            }
        ]
    }
    </script>
</x-marketing-layout>
