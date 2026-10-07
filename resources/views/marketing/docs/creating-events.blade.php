<x-docs-page
    key="creating-events"
    title="Creating Events: Tickets, Repeats, Privacy - Event Schedule"
    description="Add events to your schedule and set up each one: venue, lineup, recurrence, visibility, tickets and polls, plus how to tell attendees when plans change."
    lede="Add events to your schedule and configure event settings like venue, participants, recurrence, visibility, and tickets."
>
    <x-slot:toc>
        {{-- The form's tabs in the form's order. A tab that has more than one section here is a group. --}}
        <x-doc-nav-link href="#manual">Creating Events Manually</x-doc-nav-link>
        <x-doc-nav-group label="The Event Tab" href="#details">
            <x-doc-nav-link href="#recurring">Recurring</x-doc-nav-link>
            <x-doc-nav-link href="#venue">Location</x-doc-nav-link>
            <x-doc-nav-link href="#ai-details-generator">AI Details Generator</x-doc-nav-link>
            <x-doc-nav-link href="#custom-fields">Custom Fields</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#tickets">Tickets</x-doc-nav-link>
        <x-doc-nav-group label="Participants" href="#participants">
            <x-doc-nav-link href="#claim">Pages Created for Others</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#agenda">Agenda</x-doc-nav-link>
        <x-doc-nav-link href="#gallery">Gallery</x-doc-nav-link>
        <x-doc-nav-group label="Listing" href="#listing">
            <x-doc-nav-link href="#privacy">Internal &amp; Unlisted</x-doc-nav-link>
            <x-doc-nav-link href="#schedules">Also List On</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#google-calendar">Calendar Sync</x-doc-nav-link>
        <x-doc-nav-group label="Engagement" href="#engagement">
            <x-doc-nav-link href="#polls">Polls</x-doc-nav-link>
            <x-doc-nav-link href="#fan-content">Fan Content</x-doc-nav-link>
            <x-doc-nav-link href="#feedback">Feedback</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#sponsors">Sponsors</x-doc-nav-link>
        <x-doc-nav-link href="#whatsapp">Events via WhatsApp</x-doc-nav-link>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">An event belongs to a schedule, and you add it from that schedule's page in the admin panel. Adding events is free on every plan; the parts of the form that need a paid plan are marked with a badge below. The form opens for a schedule's owner and its admins, not for a viewer. This page follows the form tab by tab, in the order the tabs are listed.</p>

        <ol class="doc-steps">
            <li class="doc-step">
                <h4 class="doc-step-title">Open the schedule and click Add Event</h4>
                <p class="text-gray-600 dark:text-gray-300">Pick the schedule in the sidebar of the admin panel. On its <strong class="text-gray-900 dark:text-white">Schedule</strong> tab, click <strong class="text-gray-900 dark:text-white">Add Event</strong> above the calendar. The dashboard has the same button, and asks which schedule when you have more than one.</p>
                <x-doc-screenshot id="creating-events--schedule-tab" alt="A schedule's Schedule tab in the admin panel, with the Add Event button above the calendar" loading="eager" />
            </li>
            <li class="doc-step">
                <h4 class="doc-step-title">Say what, when and where</h4>
                <p class="text-gray-600 dark:text-gray-300">The first tab, <strong class="text-gray-900 dark:text-white">Event</strong>, asks for the <strong class="text-gray-900 dark:text-white">Event Name</strong> and the <strong class="text-gray-900 dark:text-white">Date &amp; Time</strong>, the two things every event must have, then a flyer image and a sub-schedule if your schedule has any. <strong class="text-gray-900 dark:text-white">Location</strong>, under them, says where: one of your saved venues, a new one, or online. <strong class="text-gray-900 dark:text-white">About</strong> opens the description.</p>
                <x-doc-screenshot id="creating-events--add-event" alt="The event form: its tabs listed in a sidebar, the Event tab open, and the save bar at the bottom" />
            </li>
            <li class="doc-step">
                <h4 class="doc-step-title">Choose how people sign up</h4>
                <p class="text-gray-600 dark:text-gray-300">Open the <strong class="text-gray-900 dark:text-white">Tickets</strong> tab and press <strong class="text-gray-900 dark:text-white">Free registration</strong>, <strong class="text-gray-900 dark:text-white">Sell tickets</strong> or <strong class="text-gray-900 dark:text-white">Tickets elsewhere</strong>. Leave it on <strong class="text-gray-900 dark:text-white">Not needed</strong> for an event people just turn up to.</p>
            </li>
            <li class="doc-step">
                <h4 class="doc-step-title">Open any other tab you need</h4>
                <p class="text-gray-600 dark:text-gray-300">Each tab shows a one-line summary of what it holds, so you can see what is set without opening it. The <a href="#tabs" class="doc-link">table below</a> says what each one covers.</p>
            </li>
            <li class="doc-step">
                <h4 class="doc-step-title">Publish, or save a draft</h4>
                <p class="text-gray-600 dark:text-gray-300">The button is in the bar at the bottom of the form. On a new event that will be public it reads <strong class="text-gray-900 dark:text-white">Publish</strong>, because that first save is what puts the event in front of people. With <strong class="text-gray-900 dark:text-white">Draft</strong> or <strong class="text-gray-900 dark:text-white">Internal</strong> chosen on the <a href="#listing" class="doc-link">Listing</a> tab it reads <strong class="text-gray-900 dark:text-white">Save draft</strong>. On an event that already exists it reads <strong class="text-gray-900 dark:text-white">Save</strong>, or <strong class="text-gray-900 dark:text-white">Save draft</strong> while Draft or Internal is chosen, and a saved draft gets a green <strong class="text-gray-900 dark:text-white">Publish</strong> button beside it for when you are ready.</p>
            </li>
        </ol>

        <h3 id="tabs" class="doc-subheading">Tabs of the Event Form</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The event form is split into tabs, listed in a sidebar on desktop and as collapsible headers on mobile. Under each tab's name is a one-line summary of what it holds, and a dot marks a tab with changes you have not saved yet. Some tabs only appear once they apply to your schedule:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Tab</th>
                        <th>What it covers</th>
                        <th>When it appears</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Event</span></td>
                        <td>Name, date and time (one-time or recurring), flyer and sub-schedule in one section; the location in a second; then the description</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Tickets</span></td>
                        <td>Free registration, tickets you sell, or a link to tickets elsewhere</td>
                        <td>Always, except for someone whose only tie to the event is a Curator schedule that lists it: its tickets and sales are not theirs to see</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Participants</span></td>
                        <td>Performers, speakers, and other people on the bill</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Agenda</span></td>
                        <td>Event parts such as sets, sessions, or talks</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Gallery</span> <x-doc-badge plan="pro" /></td>
                        <td>A photo gallery shown on the event page</td>
                        <td>Always. On a free schedule it carries a Pro lock and explains the plan, and a first event's form leaves it out</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Listing</span></td>
                        <td>Visibility, the event's link, its category, and the other schedules it appears on</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Calendar sync</span></td>
                        <td>Syncing this one event to a connected Google or Outlook calendar</td>
                        <td>Saved events, on a schedule that sends its events to a connected Google or Outlook calendar</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Engagement</span></td>
                        <td>Polls, fan content, feedback, and carpool</td>
                        <td>Always (the Carpool row only when carpooling is enabled on the schedule)</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Sponsors</span></td>
                        <td>Which sponsors this one event shows</td>
                        <td>When the schedule you are editing in, or the one the event belongs to, is on Pro</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The very first event you create gets a shorter form, so it starts with only what an event needs: the tabs after Tickets wait behind <strong class="text-gray-900 dark:text-white">More options</strong>, the flyer field sits under the location, locked upgrade controls are left out, and the button reads <strong class="text-gray-900 dark:text-white">Create Event</strong>.</p>

        <h3 id="saving" class="doc-subheading">Saving, and Coming Back to an Event</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The bar at the bottom of the form holds <strong class="text-gray-900 dark:text-white">Cancel</strong> and the save button, and one line that says what saving will do or what is in its way:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>The bar says</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Visibility: Public</span></td>
                        <td>On a new event: the visibility it will be saved with. The word is a link to the Listing tab, where you can change it.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Unsaved:</span> and tab names</td>
                        <td>Those tabs hold changes you have not saved. Each name opens its tab.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Saving publishes this event.</span></td>
                        <td>A saved event is being changed to Public. Changing a public one to anything else reads <strong>Saving hides this event from the public.</strong></td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Saving removes</span> ...</td>
                        <td>Saving would delete something, and the bar names it first: the event's ticket types and add-ons (<strong>Sell tickets</strong> no longer chosen), the times of its agenda (<strong>Show times</strong> off), or its own sponsors (another sponsor choice picked). Undo the change before saving to keep them.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Check:</span> and tab names</td>
                        <td>The save was refused, and those tabs hold what needs fixing. A name clears as soon as you change something on its tab.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Save your changes first</span></td>
                        <td>You pressed something that reloads the page, such as approving fan content or syncing to a calendar, with changes still unsaved.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">No unsaved changes</span></td>
                        <td>Nothing has changed since the event was opened or last saved.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">A refused save keeps what you typed.</strong> The form comes back with every field as you left it, ticket types, promo codes and add-ons included, so you fix what the message names and save again.</li>
            <li><strong class="text-gray-900 dark:text-white">A row you filled in and did not add is added by Save.</strong> A participant with a name, or a sponsor with a logo, typed into its form but not yet added to the list is added when you save. If it cannot be (no name, no logo), Save stops and opens that tab on it.</li>
            <li><strong class="text-gray-900 dark:text-white">Cancel asks first.</strong> With unsaved changes the bar asks <strong class="text-gray-900 dark:text-white">Discard unsaved changes?</strong> and offers <strong class="text-gray-900 dark:text-white">Keep editing</strong> or <strong class="text-gray-900 dark:text-white">Discard</strong>.</li>
            <li><strong class="text-gray-900 dark:text-white">An event that already exists</strong> opens straight on its fields. The page is titled with the event's name, with a badge for its saved visibility that opens the Listing tab, and the event's public link sits under the title with <strong class="text-gray-900 dark:text-white">Copy</strong> and <strong class="text-gray-900 dark:text-white">View</strong>.</li>
            <li><strong class="text-gray-900 dark:text-white">Shortcuts.</strong> Ctrl+S (Cmd+S on a Mac) saves, and Enter in the name of an event that has no date yet moves on to the date.</li>
        </ul>

        <h3 class="doc-subheading">Actions on a Saved Event</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once an event is saved, an <strong class="text-gray-900 dark:text-white">Actions</strong> menu appears at the top of the form:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Clone Event</strong> - open a copy of the event as a new, unsaved event.</li>
            <li><strong class="text-gray-900 dark:text-white">Save as Template</strong> <x-doc-badge plan="pro" /> - store the event as a reusable template on the Templates tab.</li>
            <li><strong class="text-gray-900 dark:text-white">Cancel event</strong> and <strong class="text-gray-900 dark:text-white">Restore event</strong> - mark the event cancelled without deleting it, then bring it back later. Cancelling can tell everyone who bought a ticket, registered or asked to hear about it; see <a href="#notify-attendees" class="doc-link">Notifying Attendees of Changes</a>. A cancelled event shows a red banner at the top of the form with its own Restore button.</li>
            <li><strong class="text-gray-900 dark:text-white">Delete Event</strong> - remove the event permanently.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">On a wide screen <strong class="text-gray-900 dark:text-white">Boost Event</strong> <x-doc-badge plan="pro" /> sits beside the Actions button rather than inside the menu; on a narrow screen it moves into the menu. See <a href="{{ route('marketing.docs.boost') }}" class="doc-link">Boost</a>.</p>

        <h3 id="other-ways" class="doc-subheading">Other Ways Events Arrive</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Import.</strong> Bring events in from a link, pasted text, a flyer image or a Google calendar. See <a href="{{ route('marketing.docs.ai_import') }}" class="doc-link">AI Import</a>.</li>
            <li><strong class="text-gray-900 dark:text-white">A copy or a template.</strong> <strong class="text-gray-900 dark:text-white">Clone Event</strong> starts a new event from an existing one, and a saved <a href="{{ route('marketing.docs.managing_schedules') }}#templates" class="doc-link">template</a> <x-doc-badge plan="pro" /> does the same for a format you repeat.</li>
            <li><strong class="text-gray-900 dark:text-white">Event requests.</strong> If your schedule <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-requests" class="doc-link">accepts event requests</a> with <strong class="text-gray-900 dark:text-white">Require Approval</strong> enabled, events submitted by other people wait for your review. Approve or reject them on the <strong class="text-gray-900 dark:text-white">Requests</strong> tab of your schedule's admin page, which only appears while there is something waiting.</li>
            <li><strong class="text-gray-900 dark:text-white">WhatsApp</strong> <x-doc-badge plan="enterprise" />. Send the details or a photo of a flyer in a message. See <a href="#whatsapp" class="doc-link">Creating Events via WhatsApp</a>.</li>
        </ul>

        <h3 id="notify-attendees" class="doc-subheading">Notifying Attendees of Changes</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Two groups can hear about a change to a published event: people who bought a ticket or registered, and people who asked to hear about it from the event page, who make up its <a href="{{ route('marketing.docs.tickets') }}#interest-list" class="doc-link">interest list</a>. On eventschedule.com, buyers and registrants are only emailed when your schedule has its own <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-email" class="doc-link">email settings</a>; a selfhosted install only needs a working mailer. The interest list is emailed either way, although on eventschedule.com a schedule without its own email settings can only reach an interest list of more than 50 people once its owner has verified a phone number.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">When you save a change.</strong> Change the date or time of a one-time event, or the venue or online link of any event, and saving first asks <strong class="text-gray-900 dark:text-white">Notify attendees of this change?</strong>, as long as there is someone to tell. Add a note of up to 280 characters if you like, check it with <strong class="text-gray-900 dark:text-white">Preview email</strong>, then choose <strong class="text-gray-900 dark:text-white">Notify attendees</strong> or <strong class="text-gray-900 dark:text-white">Don't notify</strong>. Either button saves the change. A new date or time on a recurring event is not detected, so it never asks.</li>
            <li><strong class="text-gray-900 dark:text-white">When you cancel.</strong> Choosing <strong class="text-gray-900 dark:text-white">Cancel event</strong> from the Actions menu asks <strong class="text-gray-900 dark:text-white">Cancel this event?</strong> first. When there is someone to tell, the button reads <strong class="text-gray-900 dark:text-white">Cancel and notify</strong> and the notice goes to all of them, with your note if you add one; otherwise it reads <strong class="text-gray-900 dark:text-white">Cancel event</strong>. <strong class="text-gray-900 dark:text-white">Keep event</strong> backs out.</li>
            <li>Nothing is sent for a draft. A change to a one-time event whose start time has already passed notifies nobody, and on a recurring event only people with a date still to come are told.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Cancelling keeps the event's tickets and refund records, stops any boost campaign and any installment plan still charging for it, and shows guests a notice that it has been cancelled. You can restore the event later. It does not refund anyone: refund each sale from the <a href="{{ route('marketing.docs.tickets') }}#managing-sales" class="doc-link">Sales page</a>, where a Stripe or PayPal payment goes back through the provider and any other method is marked as refunded.</p>
    </section>

    <!-- Details -->
    <section id="details" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            The Event Tab
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Event tab holds what nearly every event needs, in three blocks, top to bottom. The first has the name, the date and time (with the <a href="#recurring" class="doc-link">repeat settings</a> of a recurring event), the flyer and the sub-schedule. The second is the <a href="#venue" class="doc-link">Location</a>. The third, <strong class="text-gray-900 dark:text-white">About</strong>, is folded until you press it and holds the descriptions, the <a href="#ai-details-generator" class="doc-link">AI Generator</a> and your <a href="#custom-fields" class="doc-link">custom fields</a>; while it is closed it shows the short description, or the start of the description. How people sign up is on the <a href="#tickets" class="doc-link">Tickets</a> tab, and visibility, the event's link and its category are on the <a href="#listing" class="doc-link">Listing</a> tab.</p>

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
                        <td><span class="font-semibold text-gray-900 dark:text-white">Event Name</span></td>
                        <td>The event title (required)</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Date &amp; Time</span></td>
                        <td>The date, start time, and end time. Times are entered in your schedule's timezone, which is shown under the field along with a preview of how the start will read; if the schedule has no timezone set yet, a warning says so. An event has no stored end date: what is saved is the start plus a duration worked out from the two times. Choose <strong>Recurring</strong> beside the label to <a href="#recurring" class="doc-link">repeat the event</a>. Turn on <strong>Multi-day event</strong> and the field becomes <strong>Start Date</strong>, with a separate <strong>End Date</strong> row (and its own end time) below. The toggle is hidden on a recurring event.</td>
                    </tr>
                    <tr>
                        <td id="ai-flyer"><span class="font-semibold text-gray-900 dark:text-white">Flyer Image</span></td>
                        <td>A flyer or photo for the event. Click <strong>Choose File</strong>, drop an image onto the card, or paste one from the clipboard: a JPG or PNG under 2.5MB. On a first event the field sits under the location instead. Enterprise schedules can instead have one drawn from the event details by selecting <strong>Flyer Image</strong> in the <a href="#ai-details-generator" class="doc-link">AI Generator</a>, where you can also describe a style (for example "minimalist, blue and white").</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Sub-schedule</span></td>
                        <td>Groups events by type (for example "Live Music" or "Comedy"). It is optional: leave it on <strong class="text-gray-900 dark:text-white">None</strong> for an event that belongs to no sub-schedule. The field only appears when your schedule has sub-schedules. See <a href="{{ route('marketing.docs.creating_schedules') }}#customize-subschedules" class="doc-link">Sub-schedules</a></td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Short Description</span></td>
                        <td>A brief summary of the event (up to 200 characters). Appears as a subtitle on the event page and in schedule listings. Under <strong>About</strong>.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Description</span></td>
                        <td>Details about the event (supports markdown formatting). Under <strong>About</strong>.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Custom Fields</span> <x-doc-badge plan="pro" /></td>
                        <td>Any <a href="#custom-fields" class="doc-link">custom fields</a> you defined on the schedule appear at the bottom of <strong>About</strong>. The block is absent until you have defined at least one.</td>
                    </tr>
                </tbody>
            </table>
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
        <p class="text-gray-600 dark:text-gray-300 mb-4">A recurring event is one event that repeats on a pattern, so you do not have to add each date by hand. On the Event tab, choose <strong class="text-gray-900 dark:text-white">One-time</strong> or <strong class="text-gray-900 dark:text-white">Recurring</strong> beside <strong class="text-gray-900 dark:text-white">Date &amp; Time</strong>; the repeat settings open under the date.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">A recurring event is still a single event. It keeps one start time and one duration, so a matinee and an evening show on the same day are two separate events. The series never starts before the event's own date, so set the date to the first occurrence. A recurring event cannot also be a multi-day one: the <strong class="text-gray-900 dark:text-white">Multi-day event</strong> toggle is hidden while <strong class="text-gray-900 dark:text-white">Recurring</strong> is chosen.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The pattern decides which days the event lands on. Pick a <strong class="text-gray-900 dark:text-white">Frequency</strong>:</p>

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
            <li><strong class="text-gray-900 dark:text-white">Exclude Dates</strong> - dates to skip, such as a holiday. An excluded date is absent from the schedule: guests see nothing there, not a cancelled entry.</li>
        </ul>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Recurring events in calendars</div>
            <p>A recurring event is pushed to a connected <a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="doc-link">Google, Outlook, or CalDAV calendar</a> as a single entry on the series start date, not as a repeating appointment. Guests who want every date can subscribe to your schedule's <a href="{{ route('marketing.docs.sharing') }}#calendar-feeds" class="doc-link">live calendar feed</a>, offered as <strong class="text-gray-900 dark:text-white">Subscribe to all events from</strong> your schedule on your event and schedule pages, which lists each occurrence in the next 90 days individually.</p>
        </div>
    </section>

    <!-- Venue -->
    <section id="venue" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-1.5-1.5v18m7.5-18v18" />
            </svg>
            Location
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4"><strong class="text-gray-900 dark:text-white">Location</strong> is the second section of the Event tab and says where your event takes place. Turn on <strong class="text-gray-900 dark:text-white">In-person</strong>, <strong class="text-gray-900 dark:text-white">Online</strong>, or both.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">If you have saved venues, pick one from <strong class="text-gray-900 dark:text-white">All your venues</strong>; the ones your schedule was last at are offered above the list as <strong class="text-gray-900 dark:text-white">Recent venues</strong>. Choose <strong class="text-gray-900 dark:text-white">New Venue</strong> to type a venue name, street address and city instead. Typing the name of a venue you already have offers it, so the same place is not created twice. <strong class="text-gray-900 dark:text-white">Find or invite the venue by email or phone</strong> opens the venue's contact fields, and <strong class="text-gray-900 dark:text-white">State, postal code, website</strong> opens the rest of the address.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">In-Person Events</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">A chosen venue is one line: its name, street and city. <strong class="text-gray-900 dark:text-white">Change</strong> picks another, and <strong class="text-gray-900 dark:text-white">Edit</strong>, offered for a venue nobody has claimed yet, opens its address, with <strong class="text-gray-900 dark:text-white">Done</strong> to close it again. The list holds the venues you manage and the ones you follow that accept event requests; when it hides duplicates of the same place, a note under it links to where you can merge them. On a Venue schedule the event is at that venue, so <strong class="text-gray-900 dark:text-white">In-person</strong> stays on and there is nothing to change. A map of the location is shown on the public event page.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Map and Address Validation</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The buttons are at the end of <strong class="text-gray-900 dark:text-white">State, postal code, website</strong>. <strong class="text-gray-900 dark:text-white">View Map</strong> previews the address before you save. Where geocoding is set up (<code class="doc-inline-code">BACKEND_GOOGLE_KEY</code> on a selfhosted install), <strong class="text-gray-900 dark:text-white">Validate Address</strong> checks the address and suggests a corrected version, and <strong class="text-gray-900 dark:text-white">Accept</strong> takes it.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Online Events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tick <strong class="text-gray-900 dark:text-white">Online</strong> and paste an <strong class="text-gray-900 dark:text-white">Event URL</strong>, for example a Zoom, Meet, or Teams link. It is a single link field with no platform-specific integration behind it. The event page never shows the link itself: where there is no venue to name, it shows the link's domain, such as zoom.us, or just Online. The link appears on the ticket of everyone who registers or buys one, so give an online event registration or tickets if guests need to receive it.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Venue Contact and Notifications</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Find or invite the venue by email or phone</strong> does two things. If a venue with that email or phone already has a schedule on Event Schedule it is listed with a <strong class="text-gray-900 dark:text-white">Select</strong> button, so your event shows on its schedule too. If not, the contact is stored on the new venue's page, and on eventschedule.com an email reveals <strong class="text-gray-900 dark:text-white">Send an email to notify them</strong>, which tells the venue about the event and invites them to <a href="#claim" class="doc-link">claim their page</a>. On installs with SMS configured, a phone number offers <strong class="text-gray-900 dark:text-white">Send an SMS to notify them</strong> instead.</p>
            </div>
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
        <p class="text-gray-600 dark:text-gray-300 mb-4">Let AI fill in an event's category, flyer image, and descriptions from its name and your schedule's context. Open <strong class="text-gray-900 dark:text-white">About</strong> on the Event tab and click the <strong class="text-gray-900 dark:text-white">AI Generator</strong> button. The event needs a name before you generate.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Under <strong class="text-gray-900 dark:text-white">Select elements to generate</strong>, tick the fields you want: <strong class="text-gray-900 dark:text-white">Category</strong>, <strong class="text-gray-900 dark:text-white">Flyer Image</strong>, <strong class="text-gray-900 dark:text-white">Short Description</strong>, or <strong class="text-gray-900 dark:text-white">Description</strong>. Fields that already have a value are marked with a dot and left unticked, so nothing is overwritten by accident.</li>
            <li>Optionally add <strong class="text-gray-900 dark:text-white">Additional instructions</strong> (up to 500 characters), for example a house style for descriptions or a look for the flyer. Tick <strong class="text-gray-900 dark:text-white">Save as default for this schedule</strong> to reuse them next time.</li>
            <li>Optionally open <strong class="text-gray-900 dark:text-white">View/edit AI prompt</strong> to see and adjust the exact prompt, with <strong class="text-gray-900 dark:text-white">Reset to default</strong> to go back.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Generate</strong>.</li>
            <li>Review the <strong class="text-gray-900 dark:text-white">Preview</strong>. Each field has its own <strong class="text-gray-900 dark:text-white">Regenerate</strong> button, then <strong class="text-gray-900 dark:text-white">Accept</strong> writes the results into the form or <strong class="text-gray-900 dark:text-white">Discard</strong> throws them away. Nothing is saved until you save the event.</li>
        </ol>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Requirements and limits</div>
            <p>The button only appears when an AI key is configured, and generation is unavailable on demo schedules. On eventschedule.com a schedule below Enterprise sees the button greyed out, and pressing it opens the upgrade prompt. Text generation uses <x-link href="https://ai.google.dev/" target="_blank">Google Gemini</x-link> and flyer images use <x-link href="https://platform.openai.com/" target="_blank">OpenAI</x-link>, so a selfhosted install needs the matching keys in its environment settings. AI requests are capped per day per schedule, and the generator tells you when a limit is reached.</p>
        </div>
    </section>

    <!-- Custom Fields -->
    <section id="custom-fields" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
            </svg>
            Custom Fields <x-doc-badge plan="pro" /></h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Custom fields capture information about an event that the standard fields do not cover. You define them once on the schedule, and they then appear at the bottom of <strong class="text-gray-900 dark:text-white">About</strong> on the Event tab of every event form.</p>

        <h3 class="doc-subheading">Setting Up Custom Fields</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open your schedule in the admin panel and click <strong class="text-gray-900 dark:text-white">Edit Schedule</strong></li>
            <li>Open the <strong class="text-gray-900 dark:text-white">Customize</strong> tab and its <strong class="text-gray-900 dark:text-white">Custom Fields</strong> row</li>
            <li>Add a field, give it a name, and pick a type</li>
            <li>Save the schedule</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">You can define up to 10 custom fields per schedule, and drag them into the order you want. Their values are then filled in under <strong class="text-gray-900 dark:text-white">About</strong> on each event, where a field marked required must be answered before the event saves. See <a href="{{ route('marketing.docs.creating_schedules') }}#customize-custom-fields" class="doc-link">Custom Fields</a> for the schedule-side reference.</p>

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
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Typical uses are age restrictions, dress codes, support acts and door times. Each field's number is shown next to it on the schedule form as <code class="doc-inline-code">{custom_N}</code>, for use in <a href="{{ route('marketing.docs.event_graphics') }}#variables" class="doc-link">event graphics text templates</a> and slug patterns.</p>
    </section>

    <!-- Tickets -->
    <section id="tickets" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
            </svg>
            Tickets
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The Tickets tab is the one place that decides how people sign up. It opens on three choices; pressing one turns it on and shows what it needs right under it. <strong class="text-gray-900 dark:text-white">Not needed</strong>, under the three, switches sign-up off again and carries a tick while nothing is switched on. This is a summary: the <a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> guide covers setup, payment, sales, refunds and check-in.</p>

        <x-doc-screenshot id="creating-events--tickets-tab" alt="The Tickets tab of a new event: the three choices with Sell tickets pressed, Not needed under them, and the first ticket type with its price, quantity and type" />
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Choice</th>
                        <th>What it does</th>
                        <th>What opens under it</th>
                        <th>Plan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Free registration</span></td>
                        <td>Guests sign up on your own event page, with no payment. See <a href="{{ route('marketing.docs.tickets') }}#registration" class="doc-link">Free registration</a>.</td>
                        <td><strong>Registration Limit</strong> first, and the rest in a <strong>More options</strong> row</td>
                        <td>Unlimited on every plan</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Sell tickets</span></td>
                        <td>Sell tickets from your own event page, paid to your own account through the <strong>Payment Method</strong> you pick: Stripe, PayPal, Payfast, Invoice Ninja, a payment link, or cash. See <a href="{{ route('marketing.docs.tickets') }}#ticketing" class="doc-link">Sell tickets</a>.</td>
                        <td>The ticket types, one line each (price, quantity, type), then a row each for <strong>Payment</strong>, <strong>Options</strong>, <strong>Promo Codes</strong> and <strong>Add-ons</strong>. A row says what it holds and opens in place.</td>
                        <td>A ticket with a price needs <x-doc-badge plan="pro" />; a zero-price ticket sells on any plan</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Tickets elsewhere</span></td>
                        <td>Links out to wherever you already sell. See <a href="{{ route('marketing.docs.tickets') }}#external" class="doc-link">Tickets elsewhere</a>.</td>
                        <td><strong>Registration URL</strong> (where guests are sent), a <strong>Price</strong> with a currency selector, an optional <strong>Coupon Code</strong>, and a <strong>Discount</strong> saying what that code is worth. The price and coupon feed <a href="{{ route('marketing.docs.event_graphics') }}#text-template" class="doc-link">event graphics text templates</a>.</td>
                        <td>Every plan</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Changing the choice.</strong> If people have already signed up, pressing another choice asks first. An event that leaves <strong class="text-gray-900 dark:text-white">Sell tickets</strong> loses its ticket types and add-ons when you save, and the save bar says so before you do. What you typed under <strong class="text-gray-900 dark:text-white">Tickets elsewhere</strong> is kept while another choice is on, and cleared by <strong class="text-gray-900 dark:text-white">Not needed</strong>.</li>
            <li><strong class="text-gray-900 dark:text-white">Payment methods.</strong> The <strong class="text-gray-900 dark:text-white">Payment Method</strong> list offers what you have set up under <a href="{{ route('marketing.docs.account_settings') }}#payments" class="doc-link">Manage payment methods</a> that can take the event's <strong class="text-gray-900 dark:text-white">Currency</strong>. While a ticket has a price and nothing is set up to take the money, the tab says so under the choices and points at the <strong class="text-gray-900 dark:text-white">Payment</strong> row.</li>
            <li><strong class="text-gray-900 dark:text-white">On the free plan.</strong> Free registrations and zero-price tickets are unlimited, so an event that mixes a free ticket with priced ones keeps its buy button: the free one stays on sale while the priced rows wait for Pro. With <strong class="text-gray-900 dark:text-white">Sell tickets</strong> chosen, the tab carries a one-line note saying so. Scanning tickets at the door is free on every plan; the live check-in dashboard is <x-doc-badge plan="pro" />.</li>
            <li><strong class="text-gray-900 dark:text-white">Currency.</strong> Once an event has taken money, its <strong class="text-gray-900 dark:text-white">Currency</strong> is locked and the selector is greyed out, because a sale records no currency of its own. See <a href="{{ route('marketing.docs.tickets') }}#refunds" class="doc-link">Refunds</a>.</li>
            <li><strong class="text-gray-900 dark:text-white">People waiting.</strong> If visitors have asked to hear about the event, the tab says how many above your ticket types, as in "3 people asked to be told when tickets go on sale." The number is never shown publicly. See <a href="{{ route('marketing.docs.tickets') }}#interest-list" class="doc-link">Interest List</a>.</li>
            <li><strong class="text-gray-900 dark:text-white">The tab's title line.</strong> Once anything has sold it shows the count and a link to the event's <strong class="text-gray-900 dark:text-white">Sales</strong>. With tickets or registration on, a Pro schedule also gets <strong class="text-gray-900 dark:text-white">Embed Tickets</strong> (<strong class="text-gray-900 dark:text-white">Embed Registration</strong> on a registration-only event) for putting the form on <a href="{{ route('marketing.docs.tickets') }}#embed-widget" class="doc-link">another website</a>. An Unlisted event does not offer the embed.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The tab is hidden from someone whose only tie to the event is a Curator schedule that lists it: prices, payment setup and sales belong to the schedule that created the event.</p>
    </section>

    <!-- Participants -->
    <section id="participants" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            Participants
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Participants tab names the people performing, speaking or hosting, not the people attending. Every one of them appears on the public event page, and anyone with a schedule of their own is linked to it. Anyone you name who is not already on Event Schedule gets a page of their own at the same time, which they can claim later - see <a href="#claim" class="doc-link">Pages Created for Others</a>.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Adding Participants</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">+ Add Participant</strong>, then either pick one of <strong class="text-gray-900 dark:text-white">Your schedules</strong> from the list, or choose <strong class="text-gray-900 dark:text-white">Someone New</strong> and fill in a name (required) and an optional email. A phone number and a <strong class="text-gray-900 dark:text-white">Youtube Video URL</strong> wait behind <strong class="text-gray-900 dark:text-white">+ Phone or video link</strong>. When you leave the email field, anyone on Event Schedule with that address is offered under <strong class="text-gray-900 dark:text-white">Matching schedules</strong> with a <strong class="text-gray-900 dark:text-white">Select</strong> button. The email matters beyond your own records: it is what lets that person claim the page created for them. Click <strong class="text-gray-900 dark:text-white">Add</strong> to put them on the list. A participant you typed and did not add is still saved with the event, as long as it has a name.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">The List</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Each participant is one line with <strong class="text-gray-900 dark:text-white">Remove</strong>, and <strong class="text-gray-900 dark:text-white">Edit</strong> for anyone who has no account of their own yet. On a Talent schedule the schedule itself is on the list, marked <strong class="text-gray-900 dark:text-white">This schedule</strong>, and cannot be removed. The tab's summary in the sidebar lists the names.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Notify Participants</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">On eventschedule.com, a participant with an email address who does not already have an account gets a <strong class="text-gray-900 dark:text-white">Send an email to notify them</strong> tick box. That email tells them you added them to the event and invites them to claim their page. Where SMS is configured, a phone number offers <strong class="text-gray-900 dark:text-white">Send an SMS to notify them</strong> instead.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">When to Use</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Participants are most useful for Talent and Curator schedules, where events feature specific performers, speakers, or artists. On a Venue schedule the tab is marked <strong class="text-gray-900 dark:text-white">Optional</strong>.</p>
            </div>
        </div>
    </section>

    <!-- Pages created for others -->
    <section id="claim" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            Pages Created for Others
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Naming a performer or a venue who is not yet on Event Schedule creates a schedule for them there and then. It carries the name you typed, any contact details you added, and every date you list them on. This happens whether or not you send them an invitation, because it is what lets their name appear on your event page at all.</p>

        <h3 class="doc-subheading">What the page shows</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Your event page shows the whole lineup. Every act you named appears there by name, and an act with a page of its own, claimed or not, is linked to it, so the act and anyone else can get from your event to the page made for them.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Until it is claimed, the page opens with <strong class="text-gray-900 dark:text-white">This page was created by</strong> and the schedule that first listed them - or <strong class="text-gray-900 dark:text-white">This page was created automatically</strong>, when that cannot be worked out - and says the act has not claimed it yet, so nobody mistakes it for a page they built themselves. Below that it lists up to twenty upcoming public dates other schedules have added them to, each credited to the schedule that added it. Draft, internal, unlisted and cancelled dates are not shown, and a page whose dates have all passed says so. It carries no contact details, no follow button and no tickets, and it stays out of search engines until it is claimed.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Inviting them</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">On eventschedule.com, ticking <strong class="text-gray-900 dark:text-white">Send an email to notify them</strong> on the participant or venue sends a single email telling them you added them to the event, with a link to their page. It sends once each time you tick the box, and replies go to whoever created the event. Nothing is sent for a draft event, or to someone whose address already has an account, because in that case the page is already theirs.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">How they claim it</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">They press <strong class="text-gray-900 dark:text-white">Claim this page</strong> and sign in with a verified account on the contact the page carries - the email address if it has one, otherwise the phone number. That is the whole check, so the contact you enter decides who can claim it: a booking agent's address means the agent claims the page, not the act. Once claimed, the page is an ordinary schedule and they can edit everything on it. Where SMS is configured, verifying the phone number on an account also claims any unclaimed schedule carrying that number, as long as it was created in the past year; claiming by email has no such time limit.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">If it is not them</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">This is not me</strong> is the other answer, and it asks them to sign in first so we know who is reporting. Somebody who holds the contact on the page can take it down immediately; anyone else has their request recorded for review, so the button cannot be aimed at a competitor.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">After they claim it</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Dates you have already listed them on stay exactly where they are. If they claim by email, or by pressing Claim this page, the schedules that already had dates on their page are carried onto their approved list, so your future dates keep appearing without waiting for approval; a claim made by verifying a phone number does not carry that across. Any other schedule that lists them from then on sends a request, which they accept or decline on their <a href="{{ route('marketing.docs.managing_schedules') }}#requests" class="doc-link">Requests</a> tab. A performer cannot switch that off, because a Talent schedule always requires approval; a claimed venue decides for itself in its <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-requests" class="doc-link">request settings</a>.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">You are creating a page about someone else</div>
            <p>Enter a name they would recognise and a contact address that is really theirs. The page is public from the moment it exists, and the address on it is what decides who can take it over.</p>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Agenda tab breaks an event into parts, such as performances, sessions, or talks. Each part appears on the public event page so guests can see what to expect. Adding and editing parts by hand is free on every plan.</p>

        <h3 class="doc-subheading">Adding Parts</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Click <strong class="text-gray-900 dark:text-white">+ Add Part</strong>. Each part is one line:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Start Time</strong> and <strong class="text-gray-900 dark:text-white">End Time</strong> (optional) - when this part runs within the event</li>
            <li><strong class="text-gray-900 dark:text-white">Part Name</strong> (required) - the title of the part, for example "Opening Keynote" or "DJ Set"</li>
            <li><strong class="text-gray-900 dark:text-white">+ Add Description</strong>, under the line, opens a description for extra details. It stays showing once it has text.</li>
        </ul>

        <h3 class="doc-subheading">Show Times</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Once the agenda has a part, a <strong class="text-gray-900 dark:text-white">Show times</strong> switch sits beside the tab's title. Off means an agenda without times, for a set list for example: the time fields are hidden and a line under the title says the agenda is shown without times. Times already entered are kept until you save, the save bar says <em>Saving removes the times from this agenda</em> first, and switching it back on before you save brings them back. The choice is remembered for the schedule's next event.</p>

        <h3 class="doc-subheading">Reordering</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Drag a part by the handle at the start of its line, or use the up and down arrows at its end. The order on the form is the order on the event page.</p>

        <h3 id="agenda-import" class="doc-subheading">Importing with AI <x-doc-badge plan="enterprise" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong class="text-gray-900 dark:text-white">Import</strong> row under the parts builds the agenda from something you already have:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open the <strong class="text-gray-900 dark:text-white">Import</strong> row.</li>
            <li>Paste a set list or agenda into the box and click <strong class="text-gray-900 dark:text-white">Read This Text</strong>, or click <strong class="text-gray-900 dark:text-white">Or Choose a Photo</strong> to use a photo of a printed agenda, lineup, or setlist.</li>
            <li>Look over <strong class="text-gray-900 dark:text-white">Preview Parts</strong>. <strong class="text-gray-900 dark:text-white">Accept</strong> adds what was found to the parts already there (nothing is replaced), and <strong class="text-gray-900 dark:text-white">Discard</strong> drops it. If nothing could be read, the row says why.</li>
            <li>Save the event.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Two more controls sit in the same row:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">+ Instructions for the AI</strong> - opens an <strong class="text-gray-900 dark:text-white">AI Prompt</strong> box for telling the AI how to read your agenda's format (up to 500 characters). <strong class="text-gray-900 dark:text-white">Use these instructions for every event</strong>, under it, reuses them on this schedule's future events.</li>
            <li><strong class="text-gray-900 dark:text-white">Keep the photo</strong> - keeps the photo you chose with the event, where guests see it with the agenda. A kept photo can be removed again from the same place.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The row appears only where an <a href="{{ route('marketing.docs.selfhost.ai') }}" class="doc-link">AI key</a> is configured. On eventschedule.com it needs an Enterprise schedule: below that the row carries an Enterprise lock and opens the upgrade prompt, and a first event's form leaves it out. Reads are capped per day per schedule there, and the row says when the limit is reached. To photograph an agenda with a phone's camera instead, see <a href="{{ route('marketing.docs.scan_agenda') }}" class="doc-link">Scan Agenda</a>.</p>
    </section>

    <!-- Gallery -->
    <section id="gallery" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            Gallery
            <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">An event can show one flyer, and the Gallery tab adds as many photos as you like beside it, up to {{ \App\Utils\GalleryUtils::maxImages() }}: past editions, the room, the lineup. Guests see them on the event page and can open any photo full screen. The gallery is yours to curate; photos guests send in are separate and live under <a href="#fan-content" class="doc-link">Fan Content</a>.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Adding Photos</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">Add photos</strong>, drag photos onto the tab, or paste one. Each photo uploads as soon as you add it, so you can keep editing while they finish. Large photos are resized in your browser before they upload, and the location a phone stores in a photo is removed before it is published. JPG, PNG, WebP and GIF work; an iPhone's HEIC photos are converted when you pick them from the phone itself.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Arranging, Captions and Credits</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The first photo leads the gallery and is shown largest, so drag the photos into the order you want. Click a photo to add a caption and a photographer credit, move it, or remove it; <strong class="text-gray-900 dark:text-white">Apply this credit to all photos</strong> saves typing when one photographer shot them all. A removed photo can be brought back with <strong class="text-gray-900 dark:text-white">Undo</strong>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Fan Photos</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">When guests have sent in photos that you approved, <strong class="text-gray-900 dark:text-white">Add from fan photos</strong> copies the ones you pick into the gallery, credited to whoever took them. The copy is the gallery's own, so rejecting the fan photo later does not remove it.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Publishing</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Nothing changes on the event page until you click <strong class="text-gray-900 dark:text-white">Save</strong>, the same as every other field on the form. If photos are still uploading when you save, the form waits for them. The first time a gallery is published, the schedule page shows a link to see it as guests do. A gallery belongs to the event, so every date of a recurring event shows the same photos, and duplicating an event starts its copy with an empty gallery.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">On the Event Page</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Guests see a strip of photos they can swipe on a phone. On a wider screen the layout follows the number of photos: two or four sit side by side as equal tiles, and three or more lead with one large photo, with up to four beside it and a <strong class="text-gray-900 dark:text-white">+</strong> count on the last when there are more. With no flyer, the gallery takes the flyer's place at the top of the page. Any photo opens full screen, where guests can swipe, zoom and read the caption. Rename the heading with the <strong class="text-gray-900 dark:text-white">Gallery</strong> custom label, or hide it with the <code class="doc-inline-code">#gp-gallery</code> id (see <a href="{{ route('marketing.docs.schedule_styling') }}#hiding-sections" class="doc-link">Hiding sections</a>).</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Plans</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Galleries are part of the Pro plan, decided by the schedule the event belongs to. If that schedule's plan ends, the photos are kept but hidden from guests, and you can still remove them; they come back when the schedule upgrades again.</p>
            </div>
        </div>
    </section>

    <!-- Listing -->
    <section id="listing" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Listing
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Listing tab decides where and how the event is shown: the link it lives at, who can see it, its category, and the other schedules it appears on. Its summary in the sidebar reads, for example, "Public &middot; Concert".</p>

        <x-doc-screenshot id="creating-events--listing" alt="The Listing tab of a new event: Visibility with Public chosen, Category, and the Also list on checkboxes" />

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
                        <td><span class="font-semibold text-gray-900 dark:text-white">Event link</span></td>
                        <td>On a saved event. The link itself sits under the page title on every tab, with Copy and View; here, <strong>Edit</strong> beside it changes its ending. An ending the server refuses comes back open, with the reason. The field is left out when the event's venue and a performer on it both have claimed schedules of their own, unless you are editing from a Curator schedule.</td>
                    </tr>
                    <tr>
                        <td id="draft"><span class="font-semibold text-gray-900 dark:text-white">Visibility</span></td>
                        <td>Choose who can see the event. <strong>Public</strong> lists it for everyone. <strong>Draft</strong> keeps it visible to schedule members only while you finish editing. <strong>Internal</strong> (Enterprise) keeps it members-only permanently. <strong>Unlisted</strong> (Enterprise) hides it from your schedule but lets anyone with the direct link view it, optionally behind an <strong>Event password</strong>. One line under the choices says what the chosen one means, and follows whichever one you point at or tab to. Below Enterprise on eventschedule.com, Internal and Unlisted are shown locked and open the upgrade prompt. New events start at your schedule's default visibility, and switching a hidden event to Public warns you first. See <a href="#privacy" class="doc-link">Internal &amp; Unlisted Events</a> for exactly what each state hides.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Category</span></td>
                        <td>Pick the event's category from the list (for example Concert, Workshop, or Conference). You can edit the list under <a href="{{ route('marketing.docs.creating_schedules') }}#customize-categories" class="doc-link">Custom Categories</a>.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Also list on</span></td>
                        <td>The other schedules this event can appear on. See <a href="#schedules" class="doc-link">Also List On</a> below.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">To choose the visibility that all new events start with, use <strong class="text-gray-900 dark:text-white">Default visibility for new events</strong> in your schedule's <a href="{{ route('marketing.docs.creating_schedules') }}#settings-advanced" class="doc-link">Settings &rarr; Advanced</a>.</p>
    </section>

    <!-- Privacy -->
    <section id="privacy" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            Internal &amp; Unlisted Events <x-doc-badge plan="enterprise" /></h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Every event has a <strong class="text-gray-900 dark:text-white">Visibility</strong> setting on its <a href="#listing" class="doc-link">Listing</a> tab. <strong class="text-gray-900 dark:text-white">Public</strong> and <strong class="text-gray-900 dark:text-white">Draft</strong> are available on all plans; Enterprise schedules unlock two more states for events that should stay out of public view.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Visibility</th>
                        <th>On your schedule page</th>
                        <th>By direct link</th>
                        <th>Calendar feed, digest, graphics, newsletters</th>
                        <th>Interest sign-up</th>
                        <th>Connected calendar</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Public</span></td>
                        <td>Listed for everyone</td>
                        <td>Anyone</td>
                        <td>Included</td>
                        <td>Offered once the <a href="{{ route('marketing.docs.creating_schedules') }}#settings-advanced" class="doc-link">&ldquo;Notify me&rdquo; card</a> is switched on</td>
                        <td>Synced</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Draft</span></td>
                        <td>Members only</td>
                        <td>Members only</td>
                        <td>Excluded</td>
                        <td>Not offered</td>
                        <td>Not synced</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Internal</span> <x-doc-badge plan="enterprise" /></td>
                        <td>Members only</td>
                        <td>Members only</td>
                        <td>Excluded</td>
                        <td>Not offered</td>
                        <td>Not synced</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Unlisted</span> <x-doc-badge plan="enterprise" /></td>
                        <td>Hidden</td>
                        <td>Anyone with the link, and the password if you set one</td>
                        <td>Excluded</td>
                        <td>Not offered</td>
                        <td>Synced, marked private</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mt-4 mb-6">The digest is the automatic email your confirmed <a href="{{ route('marketing.docs.newsletters') }}#email-subscribers" class="doc-link">email subscribers</a> get, and it only ever covers public events your schedule created itself, not ones it lists from other schedules. The interest sign-up is the <a href="{{ route('marketing.docs.tickets') }}#interest-list" class="doc-link">Tell me when tickets go on sale</a> form on the event page.</p>

        <h3 class="doc-subheading">Draft and Internal</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Both keep an event to schedule members, and both are skipped by the calendar feed, the subscriber digest, event graphics, newsletters, webhooks, and calendar sync, and neither offers the interest sign-up. The difference is intent: a <strong class="text-gray-900 dark:text-white">Draft</strong> is on its way to being published, so once saved it gets a green <strong class="text-gray-900 dark:text-white">Publish</strong> button next to <strong class="text-gray-900 dark:text-white">Save draft</strong>. An <strong class="text-gray-900 dark:text-white">Internal</strong> event is never meant to go out, so it has no Publish button. Un-publishing an event that was already live also removes it from any connected calendar.</p>

        <h3 class="doc-subheading">Unlisted</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>On the event's <strong class="text-gray-900 dark:text-white">Listing</strong> tab, set <strong class="text-gray-900 dark:text-white">Visibility</strong> to <strong class="text-gray-900 dark:text-white">Unlisted</strong></li>
            <li>Optionally set an <strong class="text-gray-900 dark:text-white">Event password</strong>, which only applies to Unlisted events</li>
            <li>Save the event</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Unlisted events are hidden from your schedule page and calendar views. Visitors reach them only by direct link, and where you set a password they have to enter it before they see the event. An unlisted event is also left out of the calendar feed and the subscriber digest, and never offers the interest sign-up, with or without a password.</p>

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
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Hiding an event people asked about</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Moving a public event to Draft, Internal or Unlisted stops the tickets-on-sale email and the reminder going to the people who asked to hear about it, because visibility is checked again when each one is due.</p>
            </div>
        </div>

    </section>

    <!-- Schedules -->
    <section id="schedules" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Also List On
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6"><strong class="text-gray-900 dark:text-white">Also list on</strong>, the last field of the <a href="#listing" class="doc-link">Listing</a> tab, lists your other schedules and then, under <strong class="text-gray-900 dark:text-white">Schedules you follow</strong>, the ones you follow that take requests. Tick as many as you like and the event shows up on each of them; a schedule that reviews what it lists is marked <strong class="text-gray-900 dark:text-white">Needs approval</strong>. The event's own venue and participants are not in the list: they are decided by the Event and <a href="#participants" class="doc-link">Participants</a> tabs, and a line above the list names them.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Every schedule in the list has a verified email address or phone number, and one of these is true:</p>
        <ul class="doc-list mb-6">
            <li>You own or help manage it as an owner, admin, or viewer.</li>
            <li>You follow it and it <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-requests" class="doc-link">accepts event requests</a>. If that schedule also has request terms, an info icon next to its name shows them.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Adding the event to a schedule you are a member of publishes it there immediately. Adding it to a schedule you only follow creates a request, which appears on that schedule's Requests tab if its owner requires approval.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">When a ticked schedule is a Curator schedule with sub-schedules, a dropdown appears beneath it so you can choose which <strong class="text-gray-900 dark:text-white">sub-schedule</strong> the event belongs to there.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The list only appears when at least one schedule other than the one you are editing in is available to you. With a single schedule you will not see it.</p>
    </section>

    <!-- Google Calendar -->
    <section id="google-calendar" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Calendar Sync
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A published event is copied to your connected calendar each time you save it. The <strong class="text-gray-900 dark:text-white">Calendar sync</strong> tab is where you see that for one event and step in by hand. It appears on a saved event only, and only when the schedule is connected to a calendar and set to send its events there.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The tab has a row per calendar: <strong class="text-gray-900 dark:text-white">Google Calendar</strong>, <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong>, or both. Each row reads <strong class="text-gray-900 dark:text-white">Synced</strong> or <strong class="text-gray-900 dark:text-white">Not synced</strong>, with <strong class="text-gray-900 dark:text-white">Sync Now</strong> to copy the event at once or <strong class="text-gray-900 dark:text-white">Remove</strong> to take it off the calendar. Both act immediately, not with Save (with unsaved changes on the form they ask you to save first), and anything that goes wrong is said in the calendar's own row. An event that is not synced says why: a Draft or Internal event is not copied because only an event guests can see is, and any other event is copied the next time you save it.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">For connecting a calendar and choosing the direction of the sync, see <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-google" class="doc-link">Google Calendar</a> and <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-microsoft" class="doc-link">Outlook Calendar</a>.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">What gets synced</div>
            <p>Draft and Internal events are never pushed to a connected calendar, and un-publishing an event removes it again. An Unlisted event is synced but marked private in the calendar. A recurring event syncs as one entry on the series start date rather than as a repeating appointment.</p>
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
        <p class="text-gray-600 dark:text-gray-300 mb-4">The Engagement tab is a row for each of <strong class="text-gray-900 dark:text-white">Polls</strong>, <strong class="text-gray-900 dark:text-white">Fan Content</strong>, <strong class="text-gray-900 dark:text-white">Feedback</strong>, and <strong class="text-gray-900 dark:text-white">Carpool</strong> when carpooling is enabled on the schedule. Each row says what is set and opens in place; rows start closed, and one is open at a time. Rows with something waiting for you, such as pending fan content or suggested poll options, carry a count badge, and on a free schedule the Polls and Feedback rows carry a Pro lock.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Most of these settings decide whether an event follows the schedule-wide setting or overrides it. For the schedule-wide versions see <a href="{{ route('marketing.docs.creating_schedules') }}#engagement" class="doc-link">Engagement settings</a>, and for carpooling see <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-carpool" class="doc-link">Carpool</a>.</p>
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
            <li>Open the event, go to its <strong class="text-gray-900 dark:text-white">Engagement</strong> tab and open the <strong class="text-gray-900 dark:text-white">Polls</strong> row</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Add Poll</strong></li>
            <li>Enter your <strong class="text-gray-900 dark:text-white">Question</strong> (up to 500 characters)</li>
            <li>Add between 2 and 10 <strong class="text-gray-900 dark:text-white">Options</strong> for voters to choose from (up to 200 characters each)</li>
            <li>Save the event</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            You can add up to 5 polls per event, each with its own question and options. A poll left without a question, or with fewer than two options, stops the save: the Polls row opens on it so you can finish or remove it.
        </p>

        <h3 class="doc-subheading">How Voting Works</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Sign in required</strong> - guests must be signed in to vote, which is what keeps voting to one per person.</li>
            <li><strong class="text-gray-900 dark:text-white">Members only on Draft and Internal events</strong> - only members of your schedule can vote on those. On an Unlisted event, anyone signed in who has the link can vote, after entering the event's password if it has one.</li>
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
            The badge itself is a label, not a switch. Use the <strong class="text-gray-900 dark:text-white">Close Poll</strong> link below the poll to stop accepting votes, and <strong class="text-gray-900 dark:text-white">Reopen Poll</strong> in the same place to start again. A <strong class="text-gray-900 dark:text-white">Delete</strong> link sits next to it. All three take effect straight away, without saving the event.
        </p>
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
            <li><strong class="text-gray-900 dark:text-white">Videos</strong> - YouTube links on events and event parts</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Attendees do not need an account. By default they submit with just a name and email, which appear in your moderation queue so you know who sent what; the email is never shown publicly. If you would rather have people sign in first, turn on <strong class="text-gray-900 dark:text-white">Require an account</strong> in your schedule's Engagement settings.
        </p>

        <h3 class="doc-subheading">Per-Event Overrides <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            On a Pro schedule, the <strong class="text-gray-900 dark:text-white">Fan Content</strong> row of an event's Engagement tab has three choices per type: <strong class="text-gray-900 dark:text-white">Same as schedule</strong>, <strong class="text-gray-900 dark:text-white">Enabled</strong>, or <strong class="text-gray-900 dark:text-white">Disabled</strong>. Under each type's name the form says what the schedule's own setting is, for example <em>Schedule: enabled</em>. The row itself says what is in force, for example <em>Comments, Photos</em>, followed by <em>Same as schedule</em> while none of the three has been changed for this event. That lets you open one event up while the rest of the schedule stays closed, or the other way round.
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
            Curator schedules get a separate <strong class="text-gray-900 dark:text-white">Videos</strong> tab on the schedule admin page. It is not a moderation queue: it suggests YouTube videos for talent on upcoming events that have none yet, so you can fill in their profiles. See <a href="{{ route('marketing.docs.managing_schedules') }}#videos" class="doc-link">the Videos tab</a>.
        </p>

        <x-doc-screenshot id="fan-content--videos-tab" alt="Curator Videos tab suggesting YouTube videos for upcoming events" />
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
            The <strong class="text-gray-900 dark:text-white">Feedback</strong> row of an event's Engagement tab offers three options:
        </p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Same as schedule</strong> - follows whatever you set at the schedule level.</li>
            <li><strong class="text-gray-900 dark:text-white">Enabled</strong> - feedback emails go out for this event whatever the schedule setting is.</li>
            <li><strong class="text-gray-900 dark:text-white">Disabled</strong> - no feedback emails for this event whatever the schedule setting is.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            For more on reading and managing the responses, see <a href="{{ route('marketing.docs.tickets') }}#feedback" class="doc-link">Selling Tickets &rarr; Post-Event Feedback</a>.
        </p>
    </section>

    <!-- Sponsors -->
    <section id="sponsors" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
            </svg>
            Sponsors <x-doc-badge plan="pro" /></h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Sponsors tab, the last one of the event form, decides which sponsors this one event shows. By default an event shows the sponsors set on its schedule, unless the schedule has its sponsors hidden. The tab appears when the schedule you are editing in, or the one the event belongs to, is on Pro.</p>

        {{-- The event-settings id stays here: the tab was called Event Settings until 2026-10, and this is what it held. --}}
        <h3 id="event-settings" class="doc-subheading">The Three Choices</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Press one of three choices. The tab's summary in the sidebar says which is in force.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Same as schedule</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The event page displays the same sponsors configured on the schedule. This is the default. The choice names them on the form, says when the schedule has them hidden (the event page then shows none), or says the schedule has none.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">No sponsors</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Hide sponsors entirely on this event's page.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">This event's own</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Define sponsors for this event alone: fill in the form and click <strong class="text-gray-900 dark:text-white">Add Sponsor</strong>, then <strong class="text-gray-900 dark:text-white">+ Add Sponsor</strong> for the next. Each one needs a logo, plus an optional <strong class="text-gray-900 dark:text-white">Sponsor Name</strong>, <strong class="text-gray-900 dark:text-white">Website URL</strong>, and <strong class="text-gray-900 dark:text-white">Tier</strong> of Gold, Silver, or Bronze. Up to {{ config('app.max_sponsors') }} sponsors per event. They are shown on this event only, and the schedule's own sponsors are not changed. A sponsor you filled in and did not add is added when you save, as long as it has its logo.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Leaving "This event's own" deletes the event's list</div>
            <p>Choosing <strong>Same as schedule</strong> or <strong>No sponsors</strong> on an event that has sponsors of its own removes them when you save. The save bar reads <em>Saving removes this event's sponsors.</em> first, and choosing <strong>This event's own</strong> again before saving keeps them.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Logos are uploaded when you save the event, and only 15 new ones can be queued per save: with a long list, save partway through and then carry on. To set the sponsors every event starts with, see <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-sponsors" class="doc-link">Schedule Sponsors</a>.</p>
    </section>

    <!-- WhatsApp -->
    <section id="whatsapp" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
            </svg>
            Creating Events via WhatsApp <x-doc-badge plan="enterprise" />
        </h2>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Outside the event form, an Enterprise schedule can also take events by WhatsApp: send the details as text, or a photo of a flyer or poster, and AI reads it into an event. It works where the install has WhatsApp set up through Twilio (see the <a href="{{ route('marketing.docs.saas.twilio') }}" class="doc-link">Twilio setup guide</a>) along with an AI key for the reading.</p>

        <h3 class="doc-subheading">How It Works</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Add and verify your phone number in <a href="{{ route('marketing.docs.account_settings') }}#profile" class="doc-link">Settings</a>, under <strong class="text-gray-900 dark:text-white">Profile Information</strong></li>
            <li>Set a <strong class="text-gray-900 dark:text-white">Default schedule</strong> in the same place, unless you can edit exactly one schedule, so the event has somewhere to go</li>
            <li>Send a WhatsApp message to the Event Schedule number</li>
            <li>Include the event details as text, or attach a photo of a flyer or poster</li>
            <li>AI parses the details and creates the event on that schedule</li>
            <li>You get a reply with the event name, date, and link. If the same event already exists on your schedule, the reply links to it instead of creating a duplicate</li>
        </ol>

        <h3 class="doc-subheading">What AI Extracts</h3>
        <ul class="doc-list mb-6">
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">A message from a number that is not verified on an account, or sent to a schedule below Enterprise, gets a reply saying so and creates nothing.</p>
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
            <li><a href="{{ route('marketing.docs.ai_import') }}" class="doc-link">AI Import</a> - Import events from a link, text, images or a Google calendar</li>
            <li><a href="{{ route('marketing.docs.scan_agenda') }}" class="doc-link">Scan Agenda</a> - Photograph a printed agenda and turn it into an event's parts</li>
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
                    "name": "Open the Schedule",
                    "text": "Pick the schedule in the sidebar of the admin panel and open its Schedule tab.",
                    "url": "{{ url(route('marketing.docs.creating_events')) }}#manual"
                },
                {
                    "@type": "HowToStep",
                    "name": "Click Add Event",
                    "text": "Click the Add Event button above the calendar to open the event form.",
                    "url": "{{ url(route('marketing.docs.creating_events')) }}#manual"
                },
                {
                    "@type": "HowToStep",
                    "name": "Fill in the Event Tab",
                    "text": "Enter the event name and its date and time, add a flyer image, and open About for the description.",
                    "url": "{{ url(route('marketing.docs.creating_events')) }}#details"
                },
                {
                    "@type": "HowToStep",
                    "name": "Set the Location",
                    "text": "Pick one of your saved venues, enter a new one, or mark the event as online with a join link.",
                    "url": "{{ url(route('marketing.docs.creating_events')) }}#venue"
                },
                {
                    "@type": "HowToStep",
                    "name": "Save the Event",
                    "text": "Click Publish in the bar at the bottom of the form, or Save draft and then Publish when you are ready.",
                    "url": "{{ url(route('marketing.docs.creating_events')) }}#manual"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
