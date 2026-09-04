<x-docs-page
    key="creating-events"
    description="Learn how to add events to your schedule and configure event settings like venue, participants, recurrence, visibility, and tickets."
    lede="Add events to your schedule and configure event settings like venue, participants, recurrence, visibility, and tickets."
>
    <x-slot:toc>
        <x-doc-nav-link href="#manual">Creating Events Manually</x-doc-nav-link>
        <x-doc-nav-link href="#details">Details</x-doc-nav-link>
        <x-doc-nav-link href="#ai-details-generator">AI Details Generator</x-doc-nav-link>
        <x-doc-nav-link href="#venue">Venue</x-doc-nav-link>
        <x-doc-nav-link href="#participants">Participants</x-doc-nav-link>
        <x-doc-nav-link href="#recurring">Recurring</x-doc-nav-link>
        <x-doc-nav-link href="#agenda">Agenda</x-doc-nav-link>
        <x-doc-nav-link href="#schedules">Schedules</x-doc-nav-link>
        <x-doc-nav-link href="#google-calendar">Google Calendar</x-doc-nav-link>
        <x-doc-nav-link href="#whatsapp">WhatsApp</x-doc-nav-link>
        <x-doc-nav-link href="#tickets">Tickets</x-doc-nav-link>
        <x-doc-nav-group label="Event Settings" href="#event-settings">
            <x-doc-nav-link href="#custom-fields">Custom Fields</x-doc-nav-link>
            <x-doc-nav-link href="#privacy">Internal &amp; Unlisted</x-doc-nav-link>
            <x-doc-nav-link href="#sponsors">Sponsors</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Engagement" href="#engagement">
            <x-doc-nav-link href="#fan-content">Fan Content</x-doc-nav-link>
            <x-doc-nav-link href="#polls">Polls</x-doc-nav-link>
            <x-doc-nav-link href="#feedback">Feedback</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Manual Creation -->
    <section id="manual" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Creating Events Manually
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The simplest way to add an event is to create it manually from your schedule's admin page.</p>

        <x-doc-screenshot id="creating-events--schedule-tab" alt="Schedule event list view" loading="eager" />

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule</strong></li>
            <li>Click <strong class="text-gray-900 dark:text-white">"Add Event"</strong></li>
            <li>Fill in the <strong class="text-gray-900 dark:text-white">Details</strong> section:
                <ul class="doc-list mt-2 mb-2">
                    <li>Event name (required)</li>
                    <li>Visibility (Public or Draft, plus Internal and Unlisted on Enterprise)</li>
                    <li>Category, and a sub-schedule if your schedule has any</li>
                    <li>Date &amp; time (start and end time, in your schedule's timezone)</li>
                    <li>Flyer image, short description, and description</li>
                </ul>
            </li>
            <li>Open the <strong class="text-gray-900 dark:text-white">Venue</strong> section and pick or enter where the event takes place, or mark it as online</li>
            <li>Click <strong class="text-gray-900 dark:text-white">"Save"</strong>. If you saved it as a draft, a green <strong class="text-gray-900 dark:text-white">Publish</strong> button appears next to Save for when you are ready.</li>
        </ol>

        <x-doc-screenshot id="creating-events--add-event" alt="Add event form" />

        <h3 class="doc-subheading">Sections of the Event Form</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The event form is split into sections, listed in a sidebar on desktop and as collapsible headers on mobile. Some sections only appear once they apply to your schedule:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>What it covers</th>
                        <th>When it appears</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Details</span></td>
                        <td>Name, visibility, category, date and time, images, descriptions</td>
                        <td>Always, except on a Venue schedule looking at an event it did not create and cannot edit</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Venue</span></td>
                        <td>In-person location or online link</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Participants</span></td>
                        <td>Performers, speakers, and other people on the bill</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Recurring</span></td>
                        <td>One-time or repeating dates</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Agenda</span></td>
                        <td>Event parts such as sets, sessions, or talks</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Schedules</span></td>
                        <td>Adding the event to other schedules</td>
                        <td>When another schedule is available to you</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Google Calendar</span> and <span class="font-semibold text-gray-900 dark:text-white">Outlook Calendar</span></td>
                        <td>Syncing this one event to a connected calendar</td>
                        <td>Saved events on a schedule with that calendar connected</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Tickets</span></td>
                        <td>External ticket link, free registration, or built-in ticketing</td>
                        <td>For the account that created the event</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Settings</span></td>
                        <td>Per-event sponsor overrides</td>
                        <td>Pro schedules, for editors</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Engagement</span></td>
                        <td>Fan content, polls, feedback, and carpool</td>
                        <td>Always (carpool only when enabled on the schedule)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Actions on a Saved Event</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once an event is saved, an <strong class="text-gray-900 dark:text-white">Actions</strong> menu appears at the top of the form:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Clone Event</strong> - open a copy of the event as a new, unsaved event.</li>
            <li><strong class="text-gray-900 dark:text-white">Save as Template</strong> <x-doc-badge plan="pro" /> - store the event as a reusable template on the Templates tab.</li>
            <li><strong class="text-gray-900 dark:text-white">Cancel Event</strong> and <strong class="text-gray-900 dark:text-white">Restore Event</strong> - mark the event cancelled without deleting it, then bring it back later. A cancelled event shows a red banner at the top of the form with its own Restore button.</li>
            <li><strong class="text-gray-900 dark:text-white">Delete Event</strong> - remove the event permanently.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Cancel, Restore, and Delete need permission to delete the event, so a viewer does not see them. On a wide screen <strong class="text-gray-900 dark:text-white">Boost Event</strong> <x-doc-badge plan="pro" /> sits beside the Actions button rather than inside the menu; on a narrow screen it moves into the menu. See <a href="{{ route('marketing.docs.boost') }}" class="doc-link">Boost</a>.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Import with AI</div>
            <p>Don't want to type everything manually? You can also <a href="{{ route('marketing.docs.ai_import') }}" class="doc-link">import events using AI</a> from text or images.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Event requests</div>
            <p>If your schedule <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-requests" class="doc-link">accepts event requests</a> with <strong class="text-gray-900 dark:text-white">Require Approval</strong> enabled, events submitted by other people wait for your review. Approve or reject them on the <strong class="text-gray-900 dark:text-white">Requests</strong> tab of your schedule's admin page, which only appears while there is something waiting.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Notifying attendees of changes</div>
            <p>When you change a published event's date or time, its venue, or its online link, or when you cancel it, you can email everyone who bought a ticket or registered. A confirmation appears on save (and when you cancel) so you can send the notice, optionally with a short note. Date and time changes are only detected on one-time events; venue and online-link changes are detected on recurring events too. This needs your schedule's own <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-email" class="doc-link">email settings</a>, and nothing is sent for a draft event or for a date that has already passed. Cancelling an event keeps its tickets and refund records and shows a cancelled notice to guests, and you can restore the event later.</p>
        </div>
    </section>

    <!-- Details -->
    <section id="details" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            Details
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Details section contains the core information for your event, in the order the fields appear on the form:</p>

        <div class="doc-table-wrap">
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
                        <td><span class="font-semibold text-gray-900 dark:text-white">Event URL / Slug</span></td>
                        <td>For saved events the event URL is shown under the name with a copy button. Click <strong>Edit</strong> to change the slug.</td>
                    </tr>
                    <tr>
                        <td id="draft"><span class="font-semibold text-gray-900 dark:text-white">Visibility</span></td>
                        <td>Choose who can see the event. <strong>Public</strong> lists it for everyone. <strong>Draft</strong> keeps it visible to schedule members only while you finish editing. <strong>Internal</strong> (Enterprise) keeps it members-only permanently. <strong>Unlisted</strong> (Enterprise) hides it from your schedule but lets anyone with the direct link view it, optionally behind a password. New events start at your schedule's default visibility, and switching a hidden event to Public warns you first. See <a href="#privacy" class="doc-link">Internal &amp; Unlisted Events</a> for exactly what each state hides.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Sub-schedule</span></td>
                        <td>Groups events by type (for example "Live Music" or "Comedy"). The field is labelled <strong>Schedule</strong> on the form and only appears when your schedule has sub-schedules. See <a href="{{ route('marketing.docs.creating_schedules') }}#customize-subschedules" class="doc-link">Sub-schedules</a></td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Category</span></td>
                        <td>Select an event category from the dropdown (for example Concert, Workshop, or Conference). You can edit the list under <a href="{{ route('marketing.docs.creating_schedules') }}#customize-categories" class="doc-link">Custom Categories</a>.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Date &amp; Time</span></td>
                        <td>The date, start time, and end time. Times are entered in your schedule's timezone, which is shown under the field along with a preview of how the start will read; if the schedule has no timezone set yet, a warning says so. An event has no stored end date: what is saved is the start plus a duration worked out from the two times. Turn on <strong>Multi-day event</strong> and the field becomes <strong>Start Date</strong>, with a separate <strong>End Date</strong> row (and its own end time) below. The toggle is hidden on a recurring event.</td>
                    </tr>
                    <tr>
                        <td id="ai-flyer"><span class="font-semibold text-gray-900 dark:text-white">Flyer Image</span></td>
                        <td>A flyer or photo for the event. Click <strong>Choose File</strong> and pick a JPG or PNG under 2.5MB. Enterprise schedules can instead have one drawn from the event details by selecting <strong>Flyer Image</strong> in the <a href="#ai-details-generator" class="doc-link">AI Generator</a>, where you can also describe a style (for example "minimalist, blue and white").</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Short Description</span></td>
                        <td>A brief summary of the event (up to 200 characters). Appears as a subtitle on the event page and in schedule listings.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Description</span></td>
                        <td>Details about the event (supports markdown formatting)</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Custom Fields</span> <x-doc-badge plan="pro" /></td>
                        <td>Any <a href="#custom-fields" class="doc-link">custom fields</a> you defined on the schedule appear at the bottom of the Details section. The block is absent until you have defined at least one.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Default visibility</div>
            <p>To choose the visibility that all new events start with, use <strong>Default visibility for new events</strong> in your schedule's <a href="{{ route('marketing.docs.creating_schedules') }}#settings-advanced" class="doc-link">Settings &rarr; Advanced</a>.</p>
        </div>
    </section>

    <!-- AI Details Generator -->
    <section id="ai-details-generator" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
            </svg>
            AI Details Generator <x-doc-badge plan="enterprise" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Let AI fill in an event's category, flyer image, and descriptions from its name and your schedule's context. Click the <strong class="text-gray-900 dark:text-white">AI Generator</strong> button in the Details header. The event needs a name before you generate.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Under <strong class="text-gray-900 dark:text-white">Select elements to generate</strong>, tick the fields you want: <strong class="text-gray-900 dark:text-white">Category</strong>, <strong class="text-gray-900 dark:text-white">Flyer Image</strong>, <strong class="text-gray-900 dark:text-white">Short Description</strong>, or <strong class="text-gray-900 dark:text-white">Description</strong>. Fields that already have a value are marked with a dot and left unticked, so nothing is overwritten by accident.</li>
            <li>Optionally add <strong class="text-gray-900 dark:text-white">Additional instructions</strong> (up to 500 characters), for example a house style for descriptions or a look for the flyer. Tick <strong class="text-gray-900 dark:text-white">Save as default for this schedule</strong> to reuse them next time.</li>
            <li>Optionally open <strong class="text-gray-900 dark:text-white">View/edit AI prompt</strong> to see and adjust the exact prompt, with <strong class="text-gray-900 dark:text-white">Reset to default</strong> to go back.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Generate</strong>.</li>
            <li>Review the <strong class="text-gray-900 dark:text-white">Preview</strong>. Each field has its own <strong class="text-gray-900 dark:text-white">Regenerate</strong> button, then <strong class="text-gray-900 dark:text-white">Accept</strong> writes the results into the form or <strong class="text-gray-900 dark:text-white">Discard</strong> throws them away. Nothing is saved until you save the event.</li>
        </ol>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Requirements and limits</div>
            <p>The button only appears when an AI key is configured, and generation is unavailable on demo schedules. Text generation uses <x-link href="https://ai.google.dev/" target="_blank">Google Gemini</x-link> and flyer images use <x-link href="https://platform.openai.com/" target="_blank">OpenAI</x-link>, so a selfhosted install needs the matching keys in its environment settings. AI requests are capped per day per schedule, and the generator tells you when a limit is reached.</p>
        </div>
    </section>

    <!-- Venue -->
    <section id="venue" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-1.5-1.5v18m7.5-18v18" />
            </svg>
            Venue
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Venue section says where your event takes place. Tick <strong class="text-gray-900 dark:text-white">In-person</strong>, <strong class="text-gray-900 dark:text-white">Online</strong>, or both.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">In-Person Events</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Choose <strong class="text-gray-900 dark:text-white">Use Existing</strong> to pick a venue that has appeared on your schedule before, or <strong class="text-gray-900 dark:text-white">Create New</strong> to enter a venue name, contact details, and address. A map of the location is shown on the public event page.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Address Validation</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">After entering an address, click <strong class="text-gray-900 dark:text-white">Validate Address</strong> to check the location and generate map coordinates, then <strong class="text-gray-900 dark:text-white">Accept</strong> the corrected version. <strong class="text-gray-900 dark:text-white">View Map</strong> previews the location before you save, and <strong class="text-gray-900 dark:text-white">Done</strong> closes the venue fields.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Online Events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tick <strong class="text-gray-900 dark:text-white">Online</strong> and paste an <strong class="text-gray-900 dark:text-white">Event URL</strong>, for example a Zoom, Meet, or Teams link. It is a single link field, shown on the event page for guests to join, with no platform-specific integration behind it.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Venue Contact and Notifications</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">You can store the venue's email, phone number, and website on the venue record. On the hosted platform, entering an email for a new venue reveals <strong class="text-gray-900 dark:text-white">"Send an email to notify them"</strong> so the venue hears about the event. On installs with SMS configured, a phone number offers <strong class="text-gray-900 dark:text-white">"Send an SMS to notify them"</strong> instead.</p>
            </div>
        </div>
    </section>

    <!-- Participants -->
    <section id="participants" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            Participants
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Participants section tags performers, speakers, or other participants on an event. They appear on the public event page, linked to their own schedule pages.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Adding Participants</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">Add</strong>, then either <strong class="text-gray-900 dark:text-white">Use Existing</strong> to choose someone who has appeared on your schedule before, or <strong class="text-gray-900 dark:text-white">Create New</strong> and fill in a name (required) plus an optional email, phone number, and <strong class="text-gray-900 dark:text-white">YouTube Video URL</strong>. Click <strong class="text-gray-900 dark:text-white">Done</strong> to add them to the list, where each entry can be edited or removed.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Notify Participants</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">On the hosted platform, a participant with an email address who does not already have an account can be sent <strong class="text-gray-900 dark:text-white">"Send an email to notify them"</strong>. Where SMS is configured, a phone number offers an SMS instead.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">When to Use</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Participants are most useful for Talent and Curator schedules, where events feature specific performers, speakers, or artists. On a Venue schedule the section is marked optional.</p>
            </div>
        </div>
    </section>

    <!-- Recurring Events -->
    <section id="recurring" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Recurring
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Recurring section turns one event into a series that repeats on a pattern, so you do not have to add each date by hand. Choose <strong class="text-gray-900 dark:text-white">One-time</strong> or <strong class="text-gray-900 dark:text-white">Recurring</strong> at the top of the section.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">A recurring event is still a single event. It keeps one start time and one duration, and the pattern decides which days it lands on. Pick a <strong class="text-gray-900 dark:text-white">Frequency</strong>:</p>

        <div class="doc-table-wrap">
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
                        <td>Repeats every week. Tick the <strong>Days of the Week</strong> the event runs on (Sun, Mon, Tue, and so on).</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Every N Weeks</span></td>
                        <td>Repeats every 2 to 52 weeks, for example fortnightly. Also uses the Days of the Week checkboxes.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Monthly (same date)</span></td>
                        <td>Repeats on the same date each month, for example the 15th</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Monthly (same day of week)</span></td>
                        <td>Repeats on the same weekday each month, for example the second Tuesday</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Yearly</span></td>
                        <td>Repeats once a year on the same date</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Recurring End</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Every pattern has an end condition:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Never</strong> - the series keeps going until you change it.</li>
            <li><strong class="text-gray-900 dark:text-white">On Date</strong> - the series stops after the date you pick.</li>
            <li><strong class="text-gray-900 dark:text-white">After Events</strong> - the series stops after a set number of occurrences.</li>
        </ul>

        <h3 class="doc-subheading">Include and Exclude Dates</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Use <strong class="text-gray-900 dark:text-white">Add Date</strong> under either list to fine-tune the generated dates:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Include Dates</strong> - extra one-off dates that do not match the pattern. They are added even after the end condition has been reached, as long as they are on or after the event's own date.</li>
            <li><strong class="text-gray-900 dark:text-white">Exclude Dates</strong> - dates to skip, such as a holiday. An excluded date is simply absent from the schedule; guests see nothing there rather than a cancelled entry.</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Good to know</div>
            <p>A series never starts before the event's own date, so set the date to the first occurrence. Every occurrence uses the same start time and duration, so a matinee and an evening show on the same day need to be two separate events. Multi-day events and recurrence are mutually exclusive: the <strong class="text-gray-900 dark:text-white">Multi-day event</strong> toggle is hidden while an event is recurring.</p>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Recurring events in calendars</div>
            <p>A recurring event is pushed to a connected <a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="doc-link">Google, Outlook, or CalDAV calendar</a> as a single entry on the series start date, not as a repeating appointment. Guests who want every date should subscribe to your schedule's <a href="{{ route('marketing.docs.sharing') }}" class="doc-link">iCal feed</a>, which lists each upcoming occurrence individually.</p>
        </div>
    </section>

    <!-- Agenda -->
    <section id="agenda" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            Agenda
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Agenda section breaks an event into parts, such as performances, sessions, or talks. Each part appears on the public event page so guests can see what to expect.</p>

        <h3 class="doc-subheading">Adding Parts</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Click <strong class="text-gray-900 dark:text-white">"+ Add Part"</strong> and fill in the details:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Part Name</strong> (required) - the title of the part, for example "Opening Keynote" or "DJ Set"</li>
            <li><strong class="text-gray-900 dark:text-white">Start Time</strong> and <strong class="text-gray-900 dark:text-white">End Time</strong> (optional) - when this part runs within the event</li>
            <li><strong class="text-gray-900 dark:text-white">Description</strong> (optional) - extra details about this part</li>
        </ul>

        <h3 class="doc-subheading">Display Options</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Two toggles control what the agenda shows, on the form and on the event page:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Show times</strong> - include each part's start and end time.</li>
            <li><strong class="text-gray-900 dark:text-white">Show description</strong> - include each part's description.</li>
        </ul>

        <h3 class="doc-subheading">Reordering</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">With both display options off, parts collapse to a compact list you can drag into order by the handle. With times or descriptions shown, each part has up and down buttons instead. The order on the form is the order on the event page.</p>

        <h3 class="doc-subheading">AI Import <x-doc-badge plan="enterprise" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Enterprise schedules can build the agenda from something you already have:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Import from Image</strong> - upload a photo of a printed agenda, lineup, or setlist and AI extracts each part.</li>
            <li><strong class="text-gray-900 dark:text-white">Import from Text</strong> - paste agenda text and AI parses it into parts.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Either way the results land in a <strong class="text-gray-900 dark:text-white">Preview</strong> first, where <strong class="text-gray-900 dark:text-white">Accept</strong> adds them to the agenda and <strong class="text-gray-900 dark:text-white">Discard</strong> drops them. Three extra controls sit alongside:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">AI Prompt</strong> - custom instructions to help AI read your agenda format (up to 500 characters).</li>
            <li><strong class="text-gray-900 dark:text-white">Save agenda image</strong> - keep the uploaded image with the event. A saved image can be removed again from the same place.</li>
            <li><strong class="text-gray-900 dark:text-white">Save as default</strong> - reuse the AI prompt on future events on this schedule.</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>The import buttons only appear when an AI key is configured, and the parsing itself requires an Enterprise schedule and a <a href="{{ route('marketing.docs.ai_import') }}" class="doc-link">Gemini API key</a>. Requests are capped per day per schedule.</p>
        </div>
    </section>

    <!-- Schedules -->
    <section id="schedules" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Schedules
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Schedules section, headed <strong class="text-gray-900 dark:text-white">Add to Schedules</strong>, lists the other schedules this event can appear on. Tick as many as you like and the event shows up on each of them.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Every schedule in the list has a verified email address or phone number, and one of these is true:</p>
        <ul class="doc-list mb-6">
            <li>You own or help manage it as an owner, admin, or viewer.</li>
            <li>You follow it and it <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-requests" class="doc-link">accepts event requests</a>. If that schedule also has request terms, an info icon next to its name shows them.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Adding the event to a schedule you are a member of publishes it there immediately. Adding it to a schedule you only follow creates a request, which appears on that schedule's Requests tab if its owner requires approval.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">When a ticked schedule is a Curator schedule with sub-schedules, a dropdown appears beneath it so you can choose which <strong class="text-gray-900 dark:text-white">sub-schedule</strong> the event belongs to there.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Conditional Section</div>
            <p>This section only appears when at least one schedule other than the one you are editing in is available to you. With a single schedule you will not see it.</p>
        </div>
    </section>

    <!-- Google Calendar -->
    <section id="google-calendar" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Google Calendar
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Google Calendar section syncs this one event to your calendar. It shows whether the event is synced, with a <strong class="text-gray-900 dark:text-white">Sync to Google Calendar</strong> or <strong class="text-gray-900 dark:text-white">Remove from Google Calendar</strong> button.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The section only appears on a saved event, and only when the schedule already has Google Calendar sync connected and set to push events to Google. See <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-google" class="doc-link">Calendar Integrations</a> for setup instructions.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Schedules connected to Outlook get a matching <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong> section directly below, with the same sync and remove buttons. See <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-microsoft" class="doc-link">Outlook Calendar</a>.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">What gets synced</div>
            <p>Draft and Internal events are never pushed to a connected calendar, and un-publishing an event removes it again. An Unlisted event is synced but marked private in the calendar. A recurring event syncs as one entry on the series start date rather than as a repeating appointment.</p>
        </div>
    </section>

    <!-- WhatsApp -->
    <section id="whatsapp" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
            </svg>
            Creating Events via WhatsApp <x-doc-badge plan="enterprise" />
        </h2>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Send a WhatsApp message to create events straight from your phone. You can send the details as text, or send a photo of a flyer or poster and let AI read it.</p>

        <h3 class="doc-subheading">How It Works</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Add and verify your phone number in <a href="{{ route('marketing.docs.account_settings') }}" class="doc-link">Settings</a>, under Profile Information</li>
            <li>Make sure you have a default schedule set, or exactly one schedule you can edit, so the event has somewhere to go</li>
            <li>Send a WhatsApp message to the Event Schedule number</li>
            <li>Include the event details as text, or attach a photo of a flyer or poster</li>
            <li>AI parses the details and creates the event on that schedule</li>
            <li>You get a reply with the event name, date, and link</li>
        </ol>

        <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">What AI Extracts</h4>
            <ul class="doc-list text-sm">
                <li>Event name</li>
                <li>Date and time</li>
                <li>Duration</li>
                <li>Venue (name, email, website, address, city, state, postal code, country)</li>
                <li>Short description and description</li>
                <li>Flyer image</li>
                <li>Category</li>
                <li>Registration URL</li>
                <li>Performers, matched to existing schedules where it can</li>
                <li>Values for any <a href="#custom-fields" class="doc-link">custom fields</a> that carry an AI prompt</li>
            </ul>
        </div>

        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">Tip</div>
            <p>Works great with event flyers: snap a photo, send it, and the event is created in seconds. If the same event already exists on your schedule, the reply links to it instead of creating a duplicate.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Requires Twilio</div>
            <p>WhatsApp event creation runs through Twilio, so the platform or install has to have it configured, along with an AI key for parsing. See the <a href="{{ route('marketing.docs.saas.twilio') }}" class="doc-link">Twilio setup guide</a> for configuration details.</p>
        </div>
    </section>

    <!-- Tickets -->
    <section id="tickets" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
            </svg>
            Tickets
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The Tickets section offers three modes for an event:
        </p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">External</strong> - link out to wherever you already sell.</li>
            <li><strong class="text-gray-900 dark:text-white">Registration</strong> - free sign-up on your own event page, with an optional capacity limit. Unlimited on every plan.</li>
            <li><strong class="text-gray-900 dark:text-white">Tickets</strong> - sell tickets with built-in Stripe payments. Also available on every plan: a free schedule can sell up to 25 paid tickets a month, and Pro removes the cap and adds the QR check-in dashboard. Payouts go to your own Stripe account with no platform fee on any plan. See the full <a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> guide for setup, sales management, and check-in details.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Free registrations and zero-price tickets never count toward the monthly allowance. While a schedule is on the free plan the Tickets section carries a one-line note about the allowance; the running total for the month lives on the schedule's Plan page.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            In <strong class="text-gray-900 dark:text-white">External</strong> mode you get a <strong class="text-gray-900 dark:text-white">Registration URL</strong> (the link guests are sent to), a <strong class="text-gray-900 dark:text-white">Price</strong> with a currency selector (used in <a href="{{ route('marketing.docs.event_graphics') }}#text-template" class="doc-link">event graphics text templates</a>), an optional <strong class="text-gray-900 dark:text-white">Coupon Code</strong>, and a <strong class="text-gray-900 dark:text-white">Discount</strong> saying what that code is worth. These fields are hidden once registration or ticketing is switched on.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            The section appears only for the account that created the event. Once tickets or registration are on, a Pro schedule can also embed the purchase or sign-up form on another website from the <strong class="text-gray-900 dark:text-white">Embed tickets</strong> link (it reads <strong class="text-gray-900 dark:text-white">Embed registration</strong> when the event is registration-only). Unlisted events do not offer the embed.
        </p>
    </section>

    <!-- Event Settings -->
    <section id="event-settings" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            Event Settings
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong class="text-gray-900 dark:text-white">Settings</strong> section of the event form holds per-event <a href="#sponsors" class="doc-link">sponsor overrides</a>. It appears for editors on Pro schedules.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The other two per-event settings covered below live in the <a href="#details" class="doc-link">Details</a> section: <a href="#custom-fields" class="doc-link">custom fields</a> at the bottom of it, and <a href="#privacy" class="doc-link">visibility</a> near the top.</p>
    </section>

    <!-- Custom Fields -->
    <section id="custom-fields" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
            </svg>
            Custom Fields <x-doc-badge plan="pro" /></h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Custom fields capture information about an event that the standard fields do not cover. You define them once on the schedule, and they then appear on every event form.</p>

        <h3 class="doc-subheading">Setting Up Custom Fields</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong></li>
            <li>Open the <strong class="text-gray-900 dark:text-white">Customize</strong> section and its <strong class="text-gray-900 dark:text-white">Custom Fields</strong> tab</li>
            <li>Add a field, give it a name, and pick a type</li>
            <li>Save your settings</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">You can define up to 10 custom fields per schedule, and drag them into the order you want. Their values are then edited under <strong class="text-gray-900 dark:text-white">Details</strong> on each event. See <a href="{{ route('marketing.docs.creating_schedules') }}#customize-custom-fields" class="doc-link">Custom Fields</a> for the schedule-side reference.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Field type</th>
                        <th>What the event form shows</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Text</span></td>
                        <td>A single-line text input</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Multi-line Text</span></td>
                        <td>A text area for longer answers</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Yes/No</span></td>
                        <td>An on/off toggle</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Date</span></td>
                        <td>A date picker</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Dropdown</span></td>
                        <td>One choice from options you list</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Multi-select</span></td>
                        <td>Any number of choices from options you list</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Per-Field Options</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Required</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The field has to be filled in before an event can be saved.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Private</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Hides the value from the guest portal. Private fields show a small lock icon next to their label on the event form. The value stays visible in the admin portal and can still be rendered into graphic templates and slug patterns via <code class="doc-inline-code">{custom_N}</code>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">On request form</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">On by default: visitors submitting an event request are asked this question too. Turn it off to keep the field for your own use.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Validation Pattern</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Text fields can be held to a ready-made pattern (email, phone, URL, digits, letters and numbers) or your own regular expression, with a <strong class="text-gray-900 dark:text-white">Hint</strong> shown to whoever fills it in. The pattern is checked in the browser and again on the server.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI prompt</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">An optional instruction telling AI how to pull this field's value out of imported text or images.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Use Cases</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Track age restrictions, dress codes, support acts, door times, or anything else specific to your events. Each field's number is shown next to it as <code class="doc-inline-code">{custom_N}</code> for use in <a href="{{ route('marketing.docs.event_graphics') }}#variables" class="doc-link">event graphics text templates</a> and slug patterns.</p>
            </div>
        </div>
    </section>

    <!-- Privacy -->
    <section id="privacy" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            Internal &amp; Unlisted Events <x-doc-badge plan="enterprise" /></h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Every event has a <strong class="text-gray-900 dark:text-white">Visibility</strong> setting in its Details section. <strong class="text-gray-900 dark:text-white">Public</strong> and <strong class="text-gray-900 dark:text-white">Draft</strong> are available on all plans; Enterprise schedules unlock two more states for events that should stay out of public view.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Visibility</th>
                        <th>On your schedule page</th>
                        <th>By direct link</th>
                        <th>Feeds, graphics, newsletters</th>
                        <th>Connected calendar</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Public</span></td>
                        <td>Listed for everyone</td>
                        <td>Anyone</td>
                        <td>Included</td>
                        <td>Synced</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Draft</span></td>
                        <td>Members only</td>
                        <td>Members only</td>
                        <td>Excluded</td>
                        <td>Not synced</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Internal</span> <x-doc-badge plan="enterprise" /></td>
                        <td>Members only</td>
                        <td>Members only</td>
                        <td>Excluded</td>
                        <td>Not synced</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Unlisted</span> <x-doc-badge plan="enterprise" /></td>
                        <td>Hidden</td>
                        <td>Anyone with the link, and the password if you set one</td>
                        <td>Excluded</td>
                        <td>Synced, marked private</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Draft and Internal</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Both keep an event to schedule members, and both are skipped by feeds, event graphics, newsletters, webhooks, and calendar sync. The difference is intent: a <strong class="text-gray-900 dark:text-white">Draft</strong> is on its way to being published, so it gets a green <strong class="text-gray-900 dark:text-white">Publish</strong> button next to Save. An <strong class="text-gray-900 dark:text-white">Internal</strong> event is never meant to go out, so it has no Publish button. Un-publishing an event that was already live also removes it from any connected calendar.</p>

        <h3 class="doc-subheading">Unlisted</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>When creating or editing an event, set <strong class="text-gray-900 dark:text-white">Visibility</strong> to <strong class="text-gray-900 dark:text-white">Unlisted</strong></li>
            <li>Optionally set an <strong class="text-gray-900 dark:text-white">Event password</strong>, which only applies to Unlisted events</li>
            <li>Save the event</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Unlisted events are hidden from your schedule page and calendar views. Visitors reach them only by direct link, and where you set a password they have to enter it before they see the event.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Mix visibility states</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Visibility is set per event, not per schedule. You can freely mix Public, Draft, Internal, and Unlisted events on the same schedule. Public events appear normally while the others stay hidden.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sharing Unlisted Events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Share the event's direct link, and its password if you set one, with your intended audience by email, messaging, or any other channel. Only people with the link (and password) can view the event.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Going public later</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Switching a hidden event to Public shows a warning first, since saving makes it visible to everyone. Any password is cleared at the same time, so a published event is never left password-locked.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">If a plan lapses</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Internal and Unlisted are Enterprise states. If a schedule drops off Enterprise, the next save of a hidden event turns it into a Draft rather than making it public, and clears any event password.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>To choose the visibility all new events start with, see <a href="{{ route('marketing.docs.creating_schedules') }}#settings-advanced" class="doc-link">Advanced Settings</a>.</p>
        </div>
    </section>

    <!-- Sponsors -->
    <section id="sponsors" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
            </svg>
            Sponsors <x-doc-badge plan="pro" /></h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Override sponsor display for a single event. By default, events show whatever sponsors are configured on the schedule.</p>

        <h3 class="doc-subheading">Sponsor Mode</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">In the <strong class="text-gray-900 dark:text-white">Settings</strong> section of the event form, choose one of three modes:</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Use schedule default</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The event page displays the same sponsors configured on the schedule. This is the default.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Show no sponsors</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Hide sponsors entirely on this event's page.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Customize</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Define event-specific sponsors. Each one needs a logo, plus an optional name, URL, and tier of Gold, Silver, or Bronze. Up to {{ config('app.max_sponsors') }} sponsors per event.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Adding several logos at once</div>
            <p>Logos are uploaded when you save the event, and only 15 new ones can be queued per save. If you are adding a long list, save partway through and then carry on.</p>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>To set default sponsors for all events, configure them at the schedule level. See <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-sponsors" class="doc-link">Schedule Sponsors</a>.</p>
        </div>
    </section>

    <!-- Engagement -->
    <section id="engagement" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
            Engagement
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The Engagement section is split into tabs: <strong class="text-gray-900 dark:text-white">Fan Content</strong>, <strong class="text-gray-900 dark:text-white">Polls</strong>, <strong class="text-gray-900 dark:text-white">Feedback</strong>, and <strong class="text-gray-900 dark:text-white">Carpool</strong> when carpooling is enabled on the schedule. Tabs with something waiting for you, such as pending fan content or suggested poll options, carry a count badge.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Most of these settings decide whether an event follows the schedule-wide setting or overrides it. For the schedule-wide versions see <a href="{{ route('marketing.docs.creating_schedules') }}#engagement" class="doc-link">Engagement settings</a>, and for carpooling see <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-carpool" class="doc-link">Carpool</a>.</p>
    </section>

    <!-- Fan Content -->
    <section id="fan-content" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
            </svg>
            Fan Content</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Fan content lets your audience add to your event pages, and nothing they send appears publicly until you approve it. There are three types, each with its own toggle in your schedule's <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-fan-content" class="doc-link">Engagement settings</a> and available on every plan:
        </p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Comments</strong> - text comments on events and event parts</li>
            <li><strong class="text-gray-900 dark:text-white">Photos</strong> - photo uploads on events, capped at 25 per schedule on the free plan and unlimited on <x-doc-badge plan="pro" /></li>
            <li><strong class="text-gray-900 dark:text-white">Videos</strong> - YouTube or Vimeo links on events and event parts</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Attendees do not need an account. By default they submit with just a name and email, which appear in your moderation queue so you know who sent what; the email is never shown publicly. If you would rather have people sign in first, turn on <strong class="text-gray-900 dark:text-white">Require an account</strong> in your schedule's Engagement settings.
        </p>

        <h3 class="doc-subheading">Per-Event Overrides <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            On a Pro schedule, the <strong class="text-gray-900 dark:text-white">Fan Content</strong> tab of an event's Engagement section has a dropdown per type: <strong class="text-gray-900 dark:text-white">Use schedule default</strong>, <strong class="text-gray-900 dark:text-white">Enabled</strong>, or <strong class="text-gray-900 dark:text-white">Disabled</strong>. That lets you open one event up while the rest of the schedule stays closed, or the other way round.
        </p>

        <h3 class="doc-subheading">Moderation</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The same tab is where you review submissions, so save the event first:
        </p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Pending Approval</strong> - each comment, photo, and video is listed with the part it belongs to, the date it was submitted for, and who sent it, with <strong class="text-gray-900 dark:text-white">Approve</strong> and <strong class="text-gray-900 dark:text-white">Reject</strong> buttons.</li>
            <li><strong class="text-gray-900 dark:text-white">Approved</strong> - everything already public, which you can still <strong class="text-gray-900 dark:text-white">Reject</strong> to pull it down. Pro schedules also get a <strong class="text-gray-900 dark:text-white">Download all photos</strong> link that zips the event's photos.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Approved content appears on the public event page straight away. To hear about submissions by email, turn on the fan content notification in your schedule's <a href="{{ route('marketing.docs.creating_schedules') }}#settings-notifications" class="doc-link">Notifications settings</a>; the email tells you how many items are waiting and links to the event.
        </p>

        <h3 class="doc-subheading">Videos Tab for Curators</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Curator schedules get a separate <strong class="text-gray-900 dark:text-white">Videos</strong> tab on the schedule admin page. It is not a moderation queue: it suggests YouTube videos for talent on upcoming events that have none yet, so you can fill in their profiles. See <a href="{{ route('marketing.docs.creating_schedules') }}#videos-links" class="doc-link">Videos &amp; Links</a>.
        </p>

        <x-doc-screenshot id="fan-content--videos-tab" alt="Curator Videos tab suggesting YouTube videos for upcoming events" />
    </section>

    <!-- Polls -->
    <section id="polls" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            Polls <x-doc-badge plan="pro" /></h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Add multiple-choice questions to an event and let your guests vote on the options that matter most.
        </p>

        <h3 class="doc-subheading">Creating Polls</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open the event in your admin panel and go to <strong class="text-gray-900 dark:text-white">Engagement &rarr; Polls</strong></li>
            <li>Click <strong class="text-gray-900 dark:text-white">Add Poll</strong></li>
            <li>Enter your <strong class="text-gray-900 dark:text-white">Question</strong> (up to 500 characters)</li>
            <li>Add between 2 and 10 <strong class="text-gray-900 dark:text-white">Options</strong> for voters to choose from (up to 200 characters each)</li>
            <li>Save the event</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            You can add up to 5 polls per event, each with its own question and options.
        </p>

        <h3 class="doc-subheading">How Voting Works</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Sign in required</strong> - guests must be signed in to vote, which is what keeps voting to one per person.</li>
            <li><strong class="text-gray-900 dark:text-white">One click to vote</strong> - guests click the option they want.</li>
            <li><strong class="text-gray-900 dark:text-white">One vote per poll</strong> - votes cannot be changed afterwards. On a recurring event, votes are counted per date, so a regular can vote again for the next occurrence.</li>
            <li><strong class="text-gray-900 dark:text-white">Instant results</strong> - the results come back as soon as the vote is cast, so guests immediately see how others voted.</li>
        </ul>

        <h3 class="doc-subheading">Viewing Results</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Once a guest has voted, results are shown as bars with the count and percentage for each option, and the leading option is highlighted.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            As the organizer you can always see the results and the total vote count on the event form, whether or not you voted. Once a poll has votes its options are locked, so retitling the choices cannot change what people voted for.
        </p>

        <h3 class="doc-subheading">User-Suggested Options</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            You can let guests add their own options to a poll:
        </p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Allow users to suggest options</strong> - turn this on to let guests add options.</li>
            <li><strong class="text-gray-900 dark:text-white">Require approval for suggested options</strong> - appears once suggestions are allowed. Suggested options then wait in a pending list on the event form until you approve or reject each one.</li>
        </ul>

        <h3 class="doc-subheading">Closing and Reopening</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Each saved poll carries an <strong class="text-gray-900 dark:text-white">Active</strong> or <strong class="text-gray-900 dark:text-white">Closed</strong> badge showing its current state:
        </p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Active polls</strong> - guests can vote, and results update as votes arrive.</li>
            <li><strong class="text-gray-900 dark:text-white">Closed polls</strong> - results are still visible, but no new votes are accepted.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The badge itself is a label, not a switch. Use the <strong class="text-gray-900 dark:text-white">Close Poll</strong> button below the poll to stop accepting votes, and <strong class="text-gray-900 dark:text-white">Reopen Poll</strong> in the same place to start again. A <strong class="text-gray-900 dark:text-white">Delete</strong> button sits next to it. All three take effect straight away, without saving the event.
        </p>
    </section>

    <!-- Feedback -->
    <section id="feedback" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
            </svg>
            Feedback <x-doc-badge plan="pro" /></h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Post-event feedback emails attendees after an event ends to collect a star rating and comments. Turn it on for the whole schedule in <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-feedback" class="doc-link">Settings &rarr; Engagement &rarr; Feedback</a>, where you also choose how long after the event the request goes out. On the hosted platform this needs your schedule's own email settings.
        </p>

        <h3 class="doc-subheading">Per-Event Override</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Feedback</strong> tab of an event's Engagement section offers three options:
        </p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Use schedule default</strong> - follows whatever you set at the schedule level.</li>
            <li><strong class="text-gray-900 dark:text-white">Enabled</strong> - feedback emails go out for this event whatever the schedule setting is.</li>
            <li><strong class="text-gray-900 dark:text-white">Disabled</strong> - no feedback emails for this event whatever the schedule setting is.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            For more on reading and managing the responses, see <a href="{{ route('marketing.docs.tickets') }}#feedback" class="doc-link">Selling Tickets &rarr; Post-Event Feedback</a>.
        </p>
    </section>

    <!-- See Also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            See Also
        </h2>
        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.ai_import') }}" class="doc-link">AI Import</a> - Import events from text or images using AI</li>
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - Add tickets or free registration to your events</li>
            <li><a href="{{ route('marketing.docs.event_graphics') }}" class="doc-link">Event Graphics</a> - Create promotional images</li>
            <li><a href="{{ route('marketing.docs.sharing') }}" class="doc-link">Sharing Your Schedule</a> - Share, embed, and subscribe to your events</li>
            <li><a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="doc-link">Calendar Integrations</a> - Set up Google Calendar, Outlook, and CalDAV sync</li>
            <li><a href="{{ route('marketing.docs.managing_schedules') }}" class="doc-link">Managing Schedules</a> - Templates, cloning, and day-to-day upkeep</li>
        </ul>
    </section>


    <x-slot:schema>
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
                    "text": "Enter the event name, visibility, category, date and time, descriptions, and upload a flyer image.",
                    "url": "{{ url(route('marketing.docs.creating_events')) }}#details"
                },
                {
                    "@type": "HowToStep",
                    "name": "Add a Venue",
                    "text": "Pick an existing venue, enter a new address, or mark the event as online with a join link.",
                    "url": "{{ url(route('marketing.docs.creating_events')) }}#venue"
                },
                {
                    "@type": "HowToStep",
                    "name": "Save the Event",
                    "text": "Click Save, then Publish if you saved the event as a draft.",
                    "url": "{{ url(route('marketing.docs.creating_events')) }}#manual"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
