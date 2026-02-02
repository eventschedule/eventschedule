<x-marketing-layout>
    <x-slot name="title">Creating Events - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Creating Events</x-slot>
    <x-slot name="description">Learn how to add events to your schedule. Create events manually, import from text or images using AI, or sync from Google Calendar.</x-slot>
    <x-slot name="keywords">create events, add events, import events, AI event parsing, event calendar, flyer import</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

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
                    <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Creating Events</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Add events to your schedule manually, import from text or images using AI, or sync from external calendars.
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
                        <a href="#ai-import" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">AI Import</a>
                        <a href="#text-import" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors pl-6">From Text</a>
                        <a href="#image-import" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors pl-6">From Images/Flyers</a>
                        <a href="#agenda-scanning" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors pl-6">Agenda Scanning</a>
                        <a href="#custom-prompts" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors pl-6">Custom AI Prompts</a>
                        <a href="#calendar-sync" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Calendar Sync</a>
                        <a href="#recurring" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Recurring Events</a>
                        <a href="#event-details" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Event Details</a>
                        <a href="#managing" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Managing Events</a>
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
                        </section>

                        <!-- AI Import -->
                        <section id="ai-import" class="doc-section">
                            <h2 class="doc-heading">Let AI Do the Heavy Lifting</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Save hours of manual data entry. Paste any event text - emails, social media posts, website listings, or even flyer descriptions - and watch it transform into a ready-to-publish event in seconds.</p>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">AI-Powered</div>
                                <p>AI import uses Google Gemini to intelligently extract event name, date, time, venue, description, and more from unstructured text and images.</p>
                            </div>
                        </section>

                        <!-- Text Import -->
                        <section id="text-import" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Importing from Text</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Copy and paste event information from any source - emails, websites, social media posts - and AI will extract the event details.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule</strong> and click <strong class="text-gray-900 dark:text-white">"Import"</strong></li>
                                <li>Paste your event text into the text box</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Parse Events"</strong></li>
                                <li>Review the extracted events and make any corrections</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Import"</strong> to add them to your schedule</li>
                            </ol>

                            <div class="grid md:grid-cols-2 gap-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Example Input</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 code-block">
                                        Live Jazz Night<br>
                                        Friday, March 15th at 8pm<br>
                                        The Blue Note, 123 Main Street<br>
                                        Featuring the John Smith Trio<br>
                                        Tickets: $20
                                    </p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-emerald-500/30">
                                    <h4 class="font-semibold text-emerald-400 mb-2">AI Extracts</h4>
                                    <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
                                        <li><strong class="text-gray-900 dark:text-white">Name:</strong> Live Jazz Night</li>
                                        <li><strong class="text-gray-900 dark:text-white">Date:</strong> March 15</li>
                                        <li><strong class="text-gray-900 dark:text-white">Time:</strong> 8:00 PM</li>
                                        <li><strong class="text-gray-900 dark:text-white">Venue:</strong> The Blue Note</li>
                                        <li><strong class="text-gray-900 dark:text-white">Address:</strong> 123 Main Street</li>
                                        <li><strong class="text-gray-900 dark:text-white">Description:</strong> Featuring the John Smith Trio</li>
                                    </ul>
                                </div>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300">Review the extracted details, make any corrections, and click Import. You can process multiple events at once.</p>
                        </section>

                        <!-- Image Import -->
                        <section id="image-import" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Importing from Images/Flyers</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Upload an event flyer or poster and AI will read the text and extract event information.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule</strong> and click <strong class="text-gray-900 dark:text-white">"Import"</strong></li>
                                <li>Upload an image file (JPG, PNG, etc.)</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Parse Events"</strong></li>
                                <li>Review the extracted events</li>
                                <li>The uploaded image can automatically become the event's featured image</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Import"</strong> to add them to your schedule</li>
                            </ol>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Image Tips</div>
                                <p>For best results, use clear, high-contrast images where text is easily readable. The AI works best with images that have legible text.</p>
                            </div>
                        </section>

                        <!-- Agenda Scanning -->
                        <section id="agenda-scanning" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Scanning Printed Agendas</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Have a printed conference program, setlist, or event schedule? You can scan it to automatically populate your event's parts (sessions, acts, segments).</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Open an event and go to the <strong class="text-gray-900 dark:text-white">"Parts"</strong> section</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Scan agenda"</strong></li>
                                <li>Take a photo with your camera or upload an image of the printed agenda</li>
                                <li>AI reads each line item and creates parts automatically</li>
                                <li>Review the extracted parts and make any corrections</li>
                            </ol>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Best for</div>
                                <p>Conference programs, concert setlists, workshop agendas, and any printed schedule with line items and times.</p>
                            </div>
                        </section>

                        <!-- Custom AI Prompts -->
                        <section id="custom-prompts" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Custom AI Prompts</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">You can add custom instructions to help AI understand your specific agenda format. This is useful when your agenda uses a non-standard layout.</p>

                            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Example Prompt</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 code-block">
                                    Each line is a session. Format: time - speaker - topic. Ignore lunch breaks.
                                </p>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300">You can set a custom prompt per event, or set a default prompt for your entire schedule under <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong>.</p>
                        </section>

                        <!-- Calendar Sync -->
                        <section id="calendar-sync" class="doc-section">
                            <h2 class="doc-heading">Calendar Sync</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Sync events automatically from your existing calendars:</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Google Calendar</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Connect your Google Calendar for bidirectional sync. Events added in either place will appear in both.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">CalDAV</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Connect any CalDAV-compatible calendar (Apple Calendar, Outlook, Fastmail, etc.).</p>
                                </div>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300">To set up calendar sync, go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong> and scroll to the Calendar Sync section.</p>
                        </section>

                        <!-- Recurring Events -->
                        <section id="recurring" class="doc-section">
                            <h2 class="doc-heading">Recurring Events</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">For events that happen regularly - weekly open mics, monthly meetups, daily happy hours - you can add multiple dates at once.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Adding Multiple Dates</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>When creating an event, look for the <strong class="text-gray-900 dark:text-white">"Add more dates"</strong> option</li>
                                <li>Click to add additional dates - add as many as you need</li>
                                <li>All dates will share the same event details (name, description, venue)</li>
                                <li>Each date becomes its own event, so you can edit them individually later if needed</li>
                            </ol>

                            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Common Patterns</h4>
                                <ul class="doc-list text-sm">
                                    <li><strong class="text-gray-900 dark:text-white">Weekly:</strong> Open mic nights, trivia, live music residencies</li>
                                    <li><strong class="text-gray-900 dark:text-white">Bi-weekly:</strong> Book clubs, game nights</li>
                                    <li><strong class="text-gray-900 dark:text-white">Monthly:</strong> Networking events, first Friday art walks</li>
                                    <li><strong class="text-gray-900 dark:text-white">Custom:</strong> Festival dates, workshop series</li>
                                </ul>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Pro Tip: Use Google Calendar for True Recurrence</div>
                                <p>For events with complex recurrence patterns (every Tuesday and Thursday, first Monday of the month, etc.), create a recurring event in Google Calendar and sync it to Event Schedule. The calendar sync handles all the pattern logic automatically.</p>
                            </div>
                        </section>

                        <!-- Event Details -->
                        <section id="event-details" class="doc-section">
                            <h2 class="doc-heading">Event Details</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Here's what you can include with each event:</p>

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
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Date & Time</span></td>
                                            <td>When the event starts</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Duration</span></td>
                                            <td>How long the event lasts (in hours)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Venue</span></td>
                                            <td>Where the event takes place (name and address)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Description</span></td>
                                            <td>Details about the event (supports markdown formatting)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Image</span></td>
                                            <td>A flyer or photo for the event</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Subschedule</span></td>
                                            <td>Organize events by type (e.g., "Live Music", "Comedy")</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Registration URL</span></td>
                                            <td>Link to external registration or ticketing</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Members</span></td>
                                            <td>Tag performers, speakers, or other participants</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Managing Events -->
                        <section id="managing" class="doc-section">
                            <h2 class="doc-heading">Managing Events</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Once events are created, you can edit, clone, or delete them from your schedule.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Editing Events</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Click on any event in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule</strong> to edit it. Changes are saved immediately when you click Save.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cloning Events</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Use the clone option to duplicate an event. Great for creating similar events on different dates - just clone and change the date.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Deleting Events</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Delete events from the event edit page. <strong class="text-gray-900 dark:text-white">Warning:</strong> If the event has sold tickets, you should refund ticket holders before deleting.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Pending Events</div>
                                <p>If you have "Require Approval" enabled, submitted events appear in a pending queue. Review them in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Pending</strong> and approve or reject each one.</p>
                            </div>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Selling Tickets</a> - Add tickets to your events</li>
                                <li><a href="{{ route('marketing.docs.event_graphics') }}" class="text-cyan-400 hover:text-cyan-300">Event Graphics</a> - Create promotional images</li>
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Sharing Your Schedule</a> - Share and embed your events</li>
                                <li><a href="{{ route('marketing.docs.creating_schedules') }}" class="text-cyan-400 hover:text-cyan-300">Advanced Schedule Settings</a> - Subschedules, auto-import, and calendar sync</li>
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
        "description": "Learn how to add events to your schedule manually, import from text or images using AI, or sync from external calendars.",
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
                "url": "{{ url(route('marketing.docs.creating_events')) }}#event-details"
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
