<x-docs-page
    key="managing-schedules"
    title="Manage Schedules: Team, Requests, Followers - Event Schedule"
    description="Run a schedule day to day: the calendar, templates, appointments, event requests, followers and email subscribers, team access levels and the audit log."
    lede="Everything on the day-to-day side of a schedule: the calendar and its Actions menu, event requests, followers, team access, your plan, and the audit log."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-group label="Schedule" href="#schedule">
            <x-doc-nav-link href="#schedule-filters">Filter Events</x-doc-nav-link>
            <x-doc-nav-link href="#actions">Actions menu</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#templates">Templates</x-doc-nav-link>
        <x-doc-nav-link href="#videos">Videos</x-doc-nav-link>
        <x-doc-nav-link href="#availability">Availability</x-doc-nav-link>
        <x-doc-nav-link href="#appointments">Appointments</x-doc-nav-link>
        <x-doc-nav-link href="#seating">Seating plans</x-doc-nav-link>
        <x-doc-nav-link href="#requests">Requests</x-doc-nav-link>
        <x-doc-nav-link href="#followers">Followers</x-doc-nav-link>
        <x-doc-nav-group label="Team" href="#team">
            <x-doc-nav-link href="#transfer-ownership">Transferring Ownership</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#plan">Plan</x-doc-nav-link>
        <x-doc-nav-link href="#audit-log">Audit Log</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Click a schedule's name in the sidebar to open its admin panel: the pages you run that schedule from, day to day. Under the schedule's name is its public address, with <strong class="text-gray-900 dark:text-white">Copy</strong> beside it, and <strong class="text-gray-900 dark:text-white">View</strong> once the schedule's email address is verified. Below that is a row of tabs, each of which is its own page (on a phone the row is a dropdown). A tab with something to count shows the number beside its name.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Not every tab is offered to every schedule: some depend on the <a href="{{ route('marketing.docs.creating_schedules') }}#schedule-types" class="doc-link">schedule type</a>, some on your access level or on whether the site is eventschedule.com, and one appears only when there is something waiting for you. A tab your plan does not include is still there, and says what the plan adds.
        </p>

        <x-doc-screenshot id="managing-schedules--schedule-tab" alt="A schedule's admin panel: its name and public address, the row of tabs, and the Schedule tab's month calendar" loading="eager" />

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Tab</th>
                        <th>What it covers</th>
                        <th>When you see it</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><a href="#schedule" class="doc-link">Schedule</a></td>
                        <td>Your events, a month at a time, plus anything without a date</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><a href="#templates" class="doc-link">Templates</a></td>
                        <td>Saved event templates you can start a new event from</td>
                        <td>Owners and admins. Using it needs <x-doc-badge plan="pro" /></td>
                    </tr>
                    <tr>
                        <td><a href="#videos" class="doc-link">Videos</a></td>
                        <td>Match YouTube videos to the talent on your upcoming events</td>
                        <td>Curator schedules</td>
                    </tr>
                    <tr>
                        <td><a href="#availability" class="doc-link">Availability</a></td>
                        <td>Days you are not free to be booked</td>
                        <td>Talent schedules. Saving needs <x-doc-badge plan="enterprise" /></td>
                    </tr>
                    <tr>
                        <td><a href="#appointments" class="doc-link">Appointments</a></td>
                        <td>Bookable time slots and the bookings they produce. The number is bookings waiting for your approval</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><a href="#seating" class="doc-link">Seating plans</a></td>
                        <td>Reusable seating plans for your room, used to sell reserved seats</td>
                        <td>Venue schedules, owners and admins. Using it needs <x-doc-badge plan="enterprise" /></td>
                    </tr>
                    <tr>
                        <td><a href="#requests" class="doc-link">Requests</a></td>
                        <td>Submitted events and bookings waiting for your decision</td>
                        <td>Only while something is pending</td>
                    </tr>
                    <tr>
                        <td><a href="#followers" class="doc-link">Followers</a></td>
                        <td>People who follow the schedule or signed up by email, and your QR follow code</td>
                        <td>Always on eventschedule.com; on a selfhosted install, once someone has subscribed</td>
                    </tr>
                    <tr>
                        <td><a href="#team" class="doc-link">Team</a></td>
                        <td>Who can get into the admin panel, and at what level</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><a href="#plan" class="doc-link">Plan</a></td>
                        <td>Subscription, usage allowances, and billing</td>
                        <td>eventschedule.com only</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mt-6 mb-4">
            Two controls sit beside the schedule's name on every tab. <strong class="text-gray-900 dark:text-white">Edit Schedule</strong> opens the <a href="{{ route('marketing.docs.creating_schedules') }}" class="doc-link">schedule's settings</a>, for owners and admins. The <a href="#actions" class="doc-link">Actions</a> menu holds importing, embedding, deleting the schedule and the <a href="#audit-log" class="doc-link">Audit Log</a>, which is a page of its own and not a tab.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Until the schedule's email address is verified, a notice above the tabs says <strong class="text-gray-900 dark:text-white">Please verify the email address</strong>, with <strong class="text-gray-900 dark:text-white">Resend Email</strong> beside it. Adding events, the View link and the list of requests all wait for that.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Your access level changes what you see</div>
            <p>Schedules have three access levels: <strong>owner</strong>, <strong>admin</strong>, and <strong>viewer</strong>. Viewers get a read-only admin panel, cannot see ticket sales, and cannot open the schedule settings page at all. Following a schedule is not an access level and grants nothing in the admin panel. See <a href="#team" class="doc-link">Team</a>.</p>
        </div>
    </section>

    <!-- Schedule -->
    <section id="schedule" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Schedule
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Schedule</strong> tab is the calendar of everything on the schedule. On a wide screen it is a month grid, loaded one month at a time. On a phone it is a list of upcoming events, soonest first, with <strong class="text-gray-900 dark:text-white">Show Past Events</strong> above it when the month has earlier ones. A long list stops after 200 dates, with <strong class="text-gray-900 dark:text-white">Show more</strong> at its end.
        </p>

        <h3 id="schedule-calendar" class="doc-subheading">Reading the calendar</h3>
        <ul class="doc-list mb-6">
            <li>On the month grid, the <strong>arrow buttons</strong> step a month back or forward and <strong>This Month</strong> returns to today. The phone list has no month buttons</li>
            <li>An event that is not public carries a mark beside its name: <strong>Draft</strong> or <strong>Internal</strong></li>
            <li>Click an event to open its public page in a new tab. To change it, point at it in the grid and use the <strong>Edit</strong> link that appears, or <strong>Edit Event</strong> on a phone card</li>
            <li>On a Talent schedule, a day a team member marked as unavailable is tinted, and its info icon names who. See <a href="#availability" class="doc-link">Availability</a></li>
        </ul>

        <h3 id="schedule-add" class="doc-subheading">Adding events</h3>
        <ul class="doc-list mb-6">
            <li><strong>Add Event</strong> creates a new event by hand. It appears once the schedule's email address is verified, and is hidden from viewers</li>
            <li>On the month grid, owners and admins can click an empty part of a day to start a new event on that date</li>
            <li><strong>Use a Template</strong> sits beside Add Event once you have at least one saved <a href="#templates" class="doc-link">template</a> (Pro)</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Saving a new event brings you back here, on the event's month, where a line above the calendar confirms it with <strong class="text-gray-900 dark:text-white">Copy Link</strong> and <strong class="text-gray-900 dark:text-white">View Event</strong>, plus <strong class="text-gray-900 dark:text-white">Add location</strong> or <strong class="text-gray-900 dark:text-white">Add tickets</strong> if it was saved without them. A draft has no public page yet, so it offers <strong class="text-gray-900 dark:text-white">Edit Event</strong> instead. Your very first event gets a fuller panel with the same links. For the event form itself, see <a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a>.
        </p>

        <h3 id="schedule-filters" class="doc-subheading">Filter Events</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            <strong class="text-gray-900 dark:text-white">Filter Events</strong> opens a panel that narrows what the calendar shows. The button is always there, even when this month has nothing to filter, and it carries a count while anything is applied.
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>In the panel</th>
                        <th>What it does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Search box</strong></td>
                        <td>Matches event names, short descriptions, venues, talent, categories, agenda parts and public custom field values. On the month grid it searches the month you are viewing and says so; on a phone it searches your upcoming events</td>
                    </tr>
                    <tr>
                        <td><strong>Filters</strong></td>
                        <td>Sub-schedule, category, venue, any custom field set to <a href="{{ route('marketing.docs.creating_schedules') }}#customize-custom-fields" class="doc-link">Show as Filter</a> (Pro), and switches for free entry and online events. Only the ones that apply to the events in view are offered</td>
                    </tr>
                    <tr>
                        <td><strong>Copy Link</strong></td>
                        <td>Offered once a category or custom field filter is chosen. It copies your schedule's public address with that filter applied, ready to share or turn into a QR code</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            While a filter or search is active, a row of <strong class="text-gray-900 dark:text-white">filter chips</strong> above the calendar shows what is applied; remove one with its <strong class="text-gray-900 dark:text-white">&times;</strong> or use <strong class="text-gray-900 dark:text-white">Clear Filters</strong>. Past events are left out of the list until the filters are cleared. A sub-schedule chosen on its own shows no chips: the count on the button is what says it is on.
        </p>

        <h3 id="schedule-unscheduled" class="doc-subheading">Unscheduled</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            On Venue and Talent schedules, events with no date at all are listed in an <strong class="text-gray-900 dark:text-white">Unscheduled</strong> section below the calendar. Each card shows the talent on the event, with its picture, name and description, and links to that talent's public page. Owners and admins get two buttons on it: <strong class="text-gray-900 dark:text-white">Schedule</strong> opens the event so you can give it a date, and <strong class="text-gray-900 dark:text-white">Decline</strong> drops it from your schedule after a confirmation.
        </p>

        <h3 id="schedule-notices" class="doc-subheading">Notices on a Curator schedule</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            A Curator schedule gathers events from many sources, so its owners and admins may see one of two notices above the calendar:
        </p>
        <ul class="doc-list mb-6">
            <li><strong>Duplicate venues.</strong> When venues on your upcoming events look like the same place entered twice, a notice counts the groups and opens <a href="{{ route('marketing.docs.creating_schedules') }}#merge" class="doc-link">Merge Duplicate Venues</a></li>
            <li><strong>Events in another timezone.</strong> When upcoming public events use a different timezone than the schedule, a notice lists them, each a link to its event form, because they may publish at the wrong time in graphics and emails. <strong>Change these events</strong> moves them all to the schedule's timezone after a confirmation: their start times stay as written and are read in the schedule's timezone from then on. <strong>Dismiss</strong> hides the notice for you until the list of events changes</li>
        </ul>

        <h3 id="actions" class="doc-subheading">Actions menu</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Actions</strong> menu sits at the top right of every tab and gathers the operations that act on the schedule as a whole. On a phone it also carries <strong class="text-gray-900 dark:text-white">Edit Schedule</strong> and <strong class="text-gray-900 dark:text-white">View Schedule</strong>; on a wider screen Edit Schedule is a button of its own, and View is the link beside the schedule's address.
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>What it does</th>
                        <th>Shown to</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Import Events</strong></td>
                        <td>Opens <a href="{{ route('marketing.docs.ai_import') }}" class="doc-link">AI Import</a> to bring in events from a link, pasted text or a flyer image</td>
                        <td>Owners and admins</td>
                    </tr>
                    <tr>
                        <td><strong>Scan Agenda</strong></td>
                        <td>Reads a printed or photographed agenda and turns it into event parts</td>
                        <td>Owners and admins, on a phone or tablet, where the site has an AI provider configured. <x-doc-badge plan="enterprise" /></td>
                    </tr>
                    <tr>
                        <td><strong>Sync Events</strong></td>
                        <td>Runs a Google Calendar sync straight away, after a confirmation, instead of waiting for the next scheduled one</td>
                        <td>The owner only, once their Google account is connected and the schedule has a Google Calendar linked</td>
                    </tr>
                    <tr>
                        <td><strong>Events Graphic</strong></td>
                        <td>Builds a shareable <a href="{{ route('marketing.docs.event_graphics') }}" class="doc-link">graphic</a> of the month's events</td>
                        <td>Everyone, including viewers</td>
                    </tr>
                    <tr>
                        <td><strong>Embed Schedule</strong></td>
                        <td>Opens the embed dialog with the code to drop your calendar into another website</td>
                        <td>Everyone, including viewers</td>
                    </tr>
                    <tr>
                        <td><strong>Audit Log</strong></td>
                        <td>Opens the <a href="#audit-log" class="doc-link">audit log</a> for this schedule</td>
                        <td>Owners and admins</td>
                    </tr>
                    <tr>
                        <td><strong>Delete Schedule</strong></td>
                        <td>Permanently deletes the schedule and everything on it, after a confirmation</td>
                        <td>The owner only</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mt-6 mb-4">
            <a href="{{ route('marketing.docs.scan_agenda') }}" class="doc-link">Scan Agenda</a> is a shortcut for a device with a camera. On a wide screen you reach the same tool from the <strong class="text-gray-900 dark:text-white">Import</strong> row on the Agenda tab of the event form. On eventschedule.com a schedule below Enterprise still sees the entry, and it opens the upgrade prompt.
        </p>
        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Deleting a schedule cannot be undone</div>
            <p>Delete Schedule removes the schedule and everything on it. The confirmation says so first when the schedule still has gift cards with a balance left on them, which deleting voids, and when it has a paid <a href="#plan" class="doc-link">plan</a>: deleting the schedule cancels that plan immediately, and the rest of the billing period is not refunded.</p>
        </div>
    </section>

    <!-- Templates -->
    <section id="templates" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
            </svg>
            Templates
            <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Save any event as a reusable <strong class="text-gray-900 dark:text-white">template</strong>, then create new events from it in seconds. This suits an event you run again and again on a different date each time. The tab is offered to owners and admins; viewers do not see it.
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open a saved event in the event form and choose <strong>Save as Template</strong> from its <strong>Actions</strong> menu. The template captures the event's details, tickets, add-ons, agenda and participants.</li>
            <li>The date is deliberately left blank so you set it fresh each time. A recurring day-of-week pattern is kept; an event password, the flyer image and a series' end date are not.</li>
            <li>On the <strong>Templates</strong> tab each template is a card with its name and the day it was saved. Use <strong>Add Event</strong> on the card to start a new event from it, the pencil icon to rename it, or the bin icon to delete it after a confirmation.</li>
            <li>On the Schedule tab, <strong>Use a Template</strong> next to Add Event opens the same list without leaving the calendar.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The new event opens in the normal event form, prefilled, so nothing is committed until you save it. See <a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a>.
        </p>
        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">On the Free plan</div>
            <p>The Templates tab is still there, but instead of your templates it shows what the feature does, with a button to upgrade for the owner. Templates you already saved are kept, and reappear if you move back to Pro.</p>
        </div>
    </section>

    <!-- Videos -->
    <section id="videos" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            Videos
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Videos</strong> tab is on <strong class="text-gray-900 dark:text-white">Curator</strong> schedules only. It helps you put a video on the page of every act you list. It shows the talent appearing on your <strong class="text-gray-900 dark:text-white">upcoming accepted events</strong> who have no video yet and whose page nobody has claimed, and searches YouTube for each one by name as the page loads. An act that has claimed its page chooses its own videos, so it is not offered here.
        </p>

        <x-doc-screenshot id="managing-schedules--videos-tab" alt="Videos tab on a Curator schedule, with every upcoming act already matched to a video" />

        <ul class="doc-list mb-6">
            <li>Each act shows the <strong>event it is booked on</strong> and its date, and the event name opens that event's page in a new tab, so you can check you have the right act before picking a video</li>
            <li>Each act gets up to six suggestions, showing the <strong>thumbnail</strong>, <strong>title</strong>, <strong>channel</strong>, <strong>view count</strong> and <strong>like count</strong>, with a <strong>Watch</strong> link that opens the video on YouTube</li>
            <li>Only videos their owner allows to be played on other websites are suggested, so a saved video will not turn into YouTube's "Video unavailable" panel on your public pages</li>
            <li>Click the <strong>play button</strong> on a suggestion to watch it right there, in the same player your visitors get</li>
            <li>The closest match is <strong>preselected</strong>. Click another card to choose it instead, or click the selected card again to clear it</li>
            <li><strong>Save Videos</strong> attaches your choice to that act's schedule, where it appears on their public page</li>
            <li><strong>Skip</strong> takes the act off the list without attaching anything, so it stops coming back</li>
            <li>Either way the act disappears from the list, and once the list is empty the tab says so</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            If a video does stop working later, because its owner turned off embedding or deleted it, you do not have to come back here. <strong class="text-gray-900 dark:text-white">Remove video</strong> appears under the video on your schedule page and on the event page for anyone who can edit the schedule, and takes just that one video away. A nightly check also removes saved videos that YouTube can no longer play.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Viewers, and what the search needs</div>
            <p>Viewers can browse the suggestions and watch them, but cannot select, save or skip. The search needs the site to have a Google API key with the YouTube Data API enabled; without one the tab reports that no videos were found.</p>
        </div>
    </section>

    <!-- Availability -->
    <section id="availability" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Availability
            <x-doc-badge plan="enterprise" />
        </h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Availability</strong> tab is on <strong class="text-gray-900 dark:text-white">Talent</strong> schedules only. Each owner and admin marks the days they are not free, which answers "can we book them that night" for the people who run the schedule with you. It is a note to your team: it is never shown publicly, and marking a day does not stop anything from being booked on it.
        </p>

        <x-doc-screenshot id="managing-schedules--availability" alt="Availability tab on a Talent schedule: a month grid with the Unavailable key and a Save button above it" />

        <h3 class="doc-subheading">Setting Availability</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Click a <strong>date</strong> in the month grid to mark it. The day is tinted red and, on a wide screen, labelled <strong>Unavailable</strong>.</li>
            <li>Click the same date again to <strong>clear</strong> the mark.</li>
            <li>Click <strong>Save</strong>. The button stays disabled until you change something, so nothing is saved by accident.</li>
            <li>Save before you move to another month with the arrows. Moving loads a new page, and marks you have not saved are lost.</li>
        </ol>

        <h3 class="doc-subheading">How Team Members See It</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            You edit only your own availability, but everyone's shows up together on the <strong class="text-gray-900 dark:text-white">Schedule</strong> tab:
        </p>
        <ul class="doc-list mb-6">
            <li>Days on which someone is unavailable are tinted on the Schedule tab's month grid</li>
            <li>The info icon on such a day lists <strong>which</strong> team members are unavailable</li>
            <li>Viewers see the tab and everyone's marks on the Schedule tab, but cannot mark days of their own</li>
        </ul>

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Saving needs Enterprise</div>
            <p>On eventschedule.com a schedule below Enterprise still has the tab. A notice at the top says what the plan adds before you mark anything, and <strong>Save</strong> opens the upgrade prompt instead of saving. Selfhosted installs have no such gate.</p>
        </div>
    </section>

    <!-- Appointments -->
    <section id="appointments" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
            </svg>
            Appointments
            <x-doc-badge plan="free" />
        </h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Appointments</strong> tab is where you offer bookable time slots, Calendly-style. Create appointment types with their own duration, weekly hours and optional price, and guests book a time on your public booking page.
        </p>

        <ul class="doc-list mb-6">
            <li>The tab has two views: <strong>Appointment types</strong>, where you set up what can be booked, and <strong>Bookings</strong>, filtered by <strong>Upcoming</strong>, <strong>Pending</strong>, <strong>Past</strong> and <strong>Cancelled</strong> and searchable by name or email</li>
            <li>A <strong>Your booking page</strong> panel gives you the link to share once a type is active, with <strong>Copy Link</strong> and <strong>Preview</strong></li>
            <li>The tab's count, repeated on Bookings and on its Pending filter, is the bookings still waiting for approval, so you can see at a glance if something needs a decision. Those also appear on the <a href="#requests" class="doc-link">Requests</a> tab</li>
            <li>Bookings are private, so they never appear on your public schedule, and paid ones show up on your Sales page</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Appointments are not <a href="#availability" class="doc-link">Availability</a>. Availability marks whole days your team is not free and is visible only to your team; Appointments publish specific time slots on a public booking page that anyone can book. For the full setup, see the <a href="{{ route('marketing.docs.appointments') }}" class="doc-link">Appointments</a> guide.
        </p>

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Free plans get one appointment type</div>
            <p>Appointment booking itself is free. On eventschedule.com a free schedule can have <strong>one</strong> active appointment type; Pro removes the cap, and also adds paid bookings and the advanced scheduling rules. Selfhosted installs have no cap and no gate.</p>
        </div>
    </section>

    <!-- Seating plans -->
    <section id="seating" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
            Seating plans
            <x-doc-badge plan="enterprise" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Seating plans</strong> tab is on <strong class="text-gray-900 dark:text-white">Venue</strong> schedules only, for owners and admins. A plan is a drawing of your room that you sell specific seats from, and one plan can be reused across every date.
        </p>
        <ul class="doc-list mb-6">
            <li><strong>New plan</strong> creates a plan and opens it in the designer, where you give it a name</li>
            <li>Each plan is a card with a small picture of the room, its seats and standing places, how many events use it, how many seats have sold, and when it was last changed</li>
            <li><strong>Open designer</strong> edits the plan and <strong>Duplicate</strong> makes a copy of it</li>
            <li><strong>Delete</strong> asks first, naming the plan and how many events use it. Those events fall back to selling by quantity, and seats already sold keep their places</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            On eventschedule.com a Venue schedule below Enterprise still has the tab, which shows what the feature does instead of your plans. To draw a plan, attach it to an event and run the box office, see <a href="{{ route('marketing.docs.allocated_seating') }}" class="doc-link">Allocated Seating</a>.
        </p>
    </section>

    <!-- Requests -->
    <section id="requests" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
            </svg>
            Requests
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Requests</strong> tab holds everything waiting for your decision, and it only appears while something is actually pending. Once you have cleared the list the tab disappears again. The tab label carries the count, so you can see how many are waiting without opening it.
        </p>

        <x-doc-screenshot id="managing-schedules--requests-tab" alt="Requests tab with one request card: the event's name, the schedule asking, the date, and View, Edit, Decline and Accept" />

        <h3 id="requests-cards" class="doc-subheading">What lands here</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Each request is a card, newest first. A card says what is being asked for and who is asking, so most can be answered without opening them. Three kinds of thing arrive:
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Kind</th>
                        <th>Where it comes from</th>
                        <th>What the card shows</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>A request from your form</strong></td>
                        <td>Anyone with your public request link, unless you have turned <strong>Accept requests</strong> off</td>
                        <td>The event's name under <strong>Booking Request</strong> (on a Talent schedule) or <strong>Submit Event</strong>, the person's name after <strong>From</strong>, the date and time, the sub-schedule it was filed under, the venue they named or <strong>Online</strong>, their email address and phone number, the answers to any questions you added to your request form, and their message</td>
                    </tr>
                    <tr>
                        <td><strong>A date from another schedule</strong></td>
                        <td>A schedule that adds you to one of its events, when your schedule reviews it first. A Talent schedule always does, unless that schedule is already on its approved list</td>
                        <td>The event's name, that schedule's picture and its name after <strong>From</strong> as a link to its page, the date and time, the sub-schedule, and the opening of that schedule's description</td>
                    </tr>
                    <tr>
                        <td><strong>An appointment booking</strong></td>
                        <td>A guest booking an appointment type that has <strong>Require approval before confirming</strong> turned on</td>
                        <td>The appointment type, the guest's name, the chosen time, their email address and phone number, the price with <strong>Paid</strong> or <strong>Unpaid</strong>, and any note they left. A booking the guest has moved to a new time is marked <strong>Moved</strong>, so you can spot it in a long list</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            While the schedule's email address is still unverified the tab lists nothing, even with requests waiting: verify the address first, from the notice above the tabs.
        </p>

        <h3 class="doc-subheading">Working through the list</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong>View</strong> opens the public page for an event request so you can see the whole thing, and <strong>Edit</strong> opens it in the event form if you want to tidy it up before publishing. An appointment booking has neither: everything about it is on the card.</li>
            <li><strong>Accept</strong> publishes the event on your schedule, or confirms the booking. The person who submitted it is emailed if they sent it from an account; a Booking Form guest with no account is not, so reply to them at the address on their card. A guest who booked an appointment gets their confirmation and calendar invite. On eventschedule.com every email about a booking, to the guest or to you, needs the schedule's own <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-email" class="doc-link">email settings</a>.</li>
            <li><strong>Decline</strong> asks you to confirm, then removes it from your schedule and emails the submitter, if they sent it from an account. Declining a booking also cancels it and frees the slot, but it does not return a payment. If the booking was paid, the email telling you it was cancelled gives the amount and payment reference, so you can return the money in Stripe or your payment provider. To refund from Event Schedule instead, use <strong>Refund Ticket</strong> on the <a href="{{ route('marketing.docs.tickets') }}#managing-sales" class="doc-link">Sales page</a> before you decline: it is only offered while the sale is still paid.</li>
            <li><strong>Accept All</strong>, shown at the top once more than one request is waiting, takes everything in one go, after a confirmation that names the count. There is no bulk decline: declining is one at a time, on purpose.</li>
        </ol>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Who can act on requests</div>
            <p>Only owners and admins get the Accept, Decline and Accept All buttons. Viewers see the same cards, and can open a request, but cannot decide it. An appointment whose start time has already passed is refused rather than confirmed, and Accept All steps over it instead of failing.</p>
        </div>

        <h3 class="doc-subheading">Being told about them</h3>
        <ul class="doc-list mb-6">
            <li><strong>From your request form.</strong> Event Schedule emails owners and admins as the request arrives. A busy day does not fill your inbox: that is at most one email every 15 minutes, and anything that arrives in between is in the daily summary</li>
            <li><strong>From another schedule.</strong> An event another schedule adds you to is announced once a day</li>
            <li><strong>The setting</strong> is <strong>New event requests</strong> under <a href="{{ route('marketing.docs.creating_schedules') }}#settings-notifications" class="doc-link">Settings &rarr; Notifications</a>. It is on unless you turn it off, and it is per person rather than per schedule. Viewers are never notified</li>
            <li><strong>A shared mailbox</strong> can be added as the schedule's <a href="{{ route('marketing.docs.creating_schedules') }}#notification-email" class="doc-link">shared notification address</a>, which gets a copy of each request email</li>
            <li>If the schedule does not require approval there is nothing to notify about, and no email is sent</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            To turn requests on, choose your request form, name schedules whose submissions skip approval, and set your request terms, see <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-requests" class="doc-link">Creating Schedules: Requests</a>. Talent schedules get a shorter version of those settings, because a request to book a performer is always reviewed by hand.
        </p>
    </section>


    <!-- Followers -->
    <section id="followers" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            Followers
        </h2>
        @if(config('app.hosted'))
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Followers</strong> tab lists your audience: three figures that count it, then two lists, <a href="{{ route('marketing.docs.newsletters') }}#email-subscribers" class="doc-link">email subscribers</a> and account followers. Both lists are default recipients when you send a <a href="{{ route('marketing.docs.newsletters') }}#recipients" class="doc-link">newsletter</a>.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            People join in two ways. Somebody signed in who presses <strong class="text-gray-900 dark:text-white">Follow</strong> becomes an account follower. Somebody signed out gives their email and name, in the <strong class="text-gray-900 dark:text-white">Stay up to date</strong> panel on your schedule and event pages or in the dialog the Follow button opens for them, and becomes an email subscriber once they open the confirmation link. Nobody appears twice, because confirming a sign-up also sets up an account that follows your schedule, and those people are listed under Email subscribers with an <strong class="text-gray-900 dark:text-white">Account</strong> badge.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Pressing Follow on its own does not sign anybody up for automatic email. Confirmed email subscribers are different: they asked to hear from you, so publishing sends them <a href="{{ route('marketing.docs.newsletters') }}#email-subscribers" class="doc-link">an automatic digest</a> of the new public events your schedule creates, at most one every few days. You can turn that off under Settings &rarr; Notifications.
        </p>

        <h3 id="followers-figures" class="doc-subheading">The three figures</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Figure</th>
                        <th>Who it counts</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Can be emailed</strong></td>
                        <td>The two figures beside it, added together</td>
                    </tr>
                    <tr>
                        <td><strong>Get new-event emails</strong></td>
                        <td>Email subscribers who confirmed and have not unsubscribed</td>
                    </tr>
                    <tr>
                        <td><strong>Newsletter only</strong></td>
                        <td>People who pressed Follow while signed in. They hear from you only when you send a newsletter</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="followers-subscribers" class="doc-subheading">Email subscribers</h3>
        <ul class="doc-list mb-6">
            <li>The heading gives the total, and the line under it splits it into confirmed, awaiting confirmation and unsubscribed</li>
            <li>Each row has the person's <strong>name</strong> with their <strong>email address</strong> under it, a <strong>status</strong> and the <strong>date</strong> they signed up, ten rows to a page</li>
            <li>The status is <strong>Confirmed</strong>, <strong>Awaiting confirmation</strong> or <strong>Unsubscribed</strong>. Only confirmed addresses are ever emailed, and the tab says so while any are still waiting: they do not count as newsletter recipients until they click the link in the email sent to them</li>
            <li>An <strong>Account</strong> badge beside a name means that person also has an account here, so the schedule is on their Following page and they can manage it themselves</li>
            <li>A <strong>Website</strong> badge means the person signed up through the form embedded on your website</li>
            <li><strong>Delete</strong>, for owners and admins, removes a subscriber after a confirmation. It removes them from both lists at once, so they stop receiving newsletters as well as the digest</li>
        </ul>

        <h3 id="followers-accounts" class="doc-subheading">Followers</h3>
        <ul class="doc-list mb-6">
            <li>Each row has the follower's <strong>name</strong> with their <strong>email address</strong> under it, their own schedule if they run one, and the <strong>date</strong> they followed you</li>
            <li>Sort by name or date by clicking the column heading, and page through longer lists at the bottom, ten rows to a page</li>
            <li>There is nothing to press on a follower's row: unfollowing is theirs to do</li>
        </ul>

        <h3 id="followers-grow" class="doc-subheading">Growing the list</h3>
        <ul class="doc-list mb-6">
            <li><strong>QR Code</strong> at the top right downloads a PNG ready to print on a poster or a flyer. Scanning it opens your public schedule page, or your custom domain if you have one, scrolled to the sign-up form</li>
            <li><strong>Embed Signup Form</strong>, beside the QR code, gives you the code to put the sign-up form on your own website. See <a href="{{ route('marketing.docs.sharing') }}#embed-subscribe-form" class="doc-link">Embedding a Signup Form</a></li>
            <li>Before anyone has joined, the tab shows <strong>Your follow link</strong> with a <strong>Copy Link</strong> button. It opens the same place as the QR code</li>
        </ul>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Follower details stay private</div>
            <p>Names and email addresses are visible only to the schedule's own members, here and on the newsletter pages. They never appear on your public schedule, your embedded calendar, or your public stats. Somebody pressing Follow is told first that the schedule will see their name and email. The notice has a <strong>Don't ask me again</strong> box, so a person who ticked it on an earlier follow is not shown it again.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The QR code and the follow link are free on every plan. For more on growing your audience, see <a href="{{ route('marketing.docs.sharing') }}#followers" class="doc-link">Sharing: Followers</a>.
        </p>
        @else
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Follow</strong> button is part of the hosted version of Event Schedule (eventschedule.com). <a href="{{ route('marketing.docs.newsletters') }}#email-subscribers" class="doc-link">Email subscribers</a> work on every installation, though: the sign-up panel on your schedule and event pages is the capture surface here, and the <strong class="text-gray-900 dark:text-white">Followers</strong> tab appears as soon as you have your first subscriber, listing each address with its status and sign-up date. If your install lets people create accounts, confirming a sign-up also sets up one that follows the schedule, marked with an <strong class="text-gray-900 dark:text-white">Account</strong> badge.
        </p>
        @endif
    </section>

    <!-- Team -->
    <section id="team" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
            Team
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Team</strong> tab lists everyone who can get into this schedule's admin panel, one row a member, with their access level in the <strong class="text-gray-900 dark:text-white">Role</strong> column. Every schedule has exactly one owner: at first the person who created it, until ownership is <a href="#transfer-ownership" class="doc-link">transferred</a>.
        </p>

        <x-doc-screenshot id="managing-schedules--team-tab" alt="Team tab: a line saying what admins and viewers can do, the Add Member button, and the owner's row with Transfer ownership" />

        <h3 class="doc-subheading">Access Levels</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Level</th>
                        <th>Can</th>
                        <th>Cannot</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Owner</strong></td>
                        <td>Everything an admin can, plus change a member's level, remove members, manage the plan and billing, transfer ownership, and delete the schedule</td>
                        <td>Be removed or demoted</td>
                    </tr>
                    <tr>
                        <td><strong>Admin</strong></td>
                        <td>Run the schedule day to day: add and edit events, accept and decline requests, edit the schedule settings, sell tickets, see the schedule's sales, waitlist and check-in dashboard, refund a sale, scan tickets at the door, invite new members</td>
                        <td>Change anyone's level, remove another member, manage the plan, transfer ownership, or delete the schedule</td>
                    </tr>
                    <tr>
                        <td><strong>Viewer</strong></td>
                        <td>Read the admin panel: browse the calendar, requests, followers, team and bookings, generate a graphic, grab the embed code, and scan tickets at the door</td>
                        <td>Add or change anything, act on requests, see ticket sales, the waitlist or the check-in dashboard, or open the schedule settings page at all</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mt-6 mb-4">
            Sales, refunds, the waitlist and the check-in dashboard cover the events on your schedule, with one exception: the team of a Curator schedule does not see the sales of an event it only lists, because that money belongs to the schedule that created the event.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            <strong class="text-gray-900 dark:text-white">Following is not an access level.</strong> Someone who follows your schedule from its public page is a member of your audience, not of your team, and gets no admin panel access at all.
        </p>

        <h3 class="doc-subheading">Managing Members</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Click <strong>Add Member</strong>, which owners and admins both have, and give the person's name and email address. A phone number is optional, and <strong>Role</strong> starts on Admin. Click <strong>Save</strong>.</li>
            <li>They are emailed an invitation. On eventschedule.com, a new person you gave a phone number for is invited by text message instead, when text messaging is configured.</li>
            <li>Until they have signed up, their row is marked <strong>Pending</strong> and has a <strong>Resend Invite</strong> link, and, on eventschedule.com, a second <strong>Resend by text message</strong> link when you gave a phone number and text messaging is configured.</li>
            <li>Once they have signed up, the owner can change their level between <strong>Admin</strong> and <strong>Viewer</strong> from the dropdown in that row. It saves as soon as you pick.</li>
            <li><strong>Remove</strong> revokes access, after a confirmation. Only the owner can remove someone else; anyone else can take themselves off with <strong>Leave</strong> on their own row, which is the one marked <strong>You</strong>. The owner's own row has neither.</li>
            <li>Sort the list by name by clicking the column heading.</li>
        </ol>

        <div class="doc-callout doc-callout-plan mb-6">
            <div class="doc-callout-title">Team size by plan <x-doc-badge plan="enterprise" /></div>
            <p>On the <strong>Free</strong> and <strong>Pro</strong> plans a schedule has a single member: you. Adding anyone else needs the <strong>Enterprise</strong> plan. Below it, on eventschedule.com, the Add Member button carries a lock and opens the upgrade prompt. On eventschedule.com a team is capped at <strong>5 members</strong> in total. Selfhosted installs count as Enterprise, so the button is available there without a subscription, and with no cap.</p>
        </div>

        <h3 id="team-plan-lapse" class="doc-subheading">If the plan lapses</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            On eventschedule.com, invited members can only open the admin panel while the schedule is on Enterprise. If it drops to a lower plan they are turned away with a message asking the owner to upgrade, and the owner keeps full access on their own. The Team tab tells the owner so, with a notice above the list that team members cannot see the schedule's ticket sales or check-ins. Nobody is removed, so restoring Enterprise restores their access.
        </p>

        <h3 id="transfer-ownership" class="doc-subheading">Transferring Ownership</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Ownership can be handed to another account: a venue changes hands, an organizer leaves, or you set a schedule up for someone and want it to be theirs. It is available on every plan, and only the owner can start it. A schedule that nobody owns yet changes hands a different way, by being claimed - see <a href="{{ route('marketing.docs.creating_events') }}#claim" class="doc-link">Pages Created for Others</a>.
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>On the Team tab click <strong>Transfer ownership</strong> on your own row. The page says what happens when they accept; enter the new owner's email address.</li>
            <li>On <strong>Enterprise</strong> and selfhosted installs you can turn off <strong>Remove me from this schedule</strong> to stay on as an admin afterwards. Free and Pro schedules hold a single member, so there you are always removed.</li>
            <li>Click <strong>Send transfer request</strong> and confirm. They are emailed a link. Nothing moves yet: a notice on the Team tab names who you are waiting for and the day the request expires, seven days on, with <strong>Resend Invite</strong> and <strong>Cancel</strong>.</li>
            <li>To accept, they sign in with the address you sent it to. The link on its own is not enough, and if they do not have an account yet they can create one with that address. They can also decline: you are emailed, and nothing changes.</li>
            <li>As soon as they accept, the schedule is theirs: every event, follower, image and setting comes with it.</li>
        </ol>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">What changes for the previous owner</div>
            <p>Ticket payments for this schedule's events start settling into the new owner's payment account, so the new owner should check their payment settings before the next sale. The previous owner's calendar sync for the schedule is disconnected, and their events, followers and settings all move across. Events curated in from other schedules are untouched: those still belong to whoever created them.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            <strong class="text-gray-900 dark:text-white">Billing on eventschedule.com.</strong> The previous owner is never charged for the schedule again: their subscription is cancelled at the end of the billing period already paid for and their saved card is removed. The schedule keeps its plan until that period ends. Before then the new owner adds their own billing details to keep it, otherwise the schedule moves to the free plan. Selfhosted installs have no billing step at all.
        </p>
    </section>

    <!-- Plan -->
    <section id="plan" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
            </svg>
            Plan
        </h2>
        @if(config('app.hosted'))
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Plan</strong> tab shows this schedule's subscription and what you have used of it. Plans are per schedule, so each schedule you run has its own.
        </p>

        <h3 id="plan-shows" class="doc-subheading">What it shows</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Line</th>
                        <th>What it says</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Current Plan</strong></td>
                        <td>Free Plan, Pro Plan or Enterprise and, for a paid plan, the billing term: monthly or yearly</td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>Trial, Active, Cancelled, Past Due or Inactive. Shown once the schedule has a subscription or a trial</td>
                    </tr>
                    <tr>
                        <td><strong>Trial Ends</strong>, <strong>Access Until</strong> or <strong>Expires On</strong></td>
                        <td>The day a trial ends, with the days remaining; the day a cancelled subscription stops; or the day a plan held without a subscription runs out</td>
                    </tr>
                    <tr>
                        <td><strong>Payment Method</strong></td>
                        <td>The card on file, by type and last four digits. The line is left out when there is none</td>
                    </tr>
                    <tr>
                        <td><strong>Newsletter Email Usage</strong></td>
                        <td>Newsletter emails sent this month against the plan's allowance, and how many remain</td>
                    </tr>
                    <tr>
                        <td><strong>Photo Usage</strong></td>
                        <td>Fan photos on your events against the 25 a free schedule can hold. Pro and Enterprise read as unlimited</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Three more things appear when they apply. While a trial has 30 days or fewer to run, the owner sees a notice at the top with the days left and a prompt to add a payment method. While a <a href="{{ route('marketing.docs.tickets') }}#selling-trial" class="doc-link">selling trial</a> is running, a <strong class="text-gray-900 dark:text-white">Paid ticket selling trial</strong> card says how many days it has left; it is not a plan, so the plan still reads Free. And a free schedule gets a <strong class="text-gray-900 dark:text-white">Your plan</strong> panel that lists what Free includes and what Pro adds, with the price.
        </p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">The newsletter meter counts recipients</div>
            <p>Each recipient counts as one email, so sending a single newsletter to 100 followers uses 100 of the allowance. Free schedules get 10 a month, Pro 100, Enterprise 1,000. A schedule that sends through its own email settings is unlimited, and so are selfhosted installs.</p>
        </div>

        <h3 id="plan-actions" class="doc-subheading">What you can do</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            These buttons are the schedule owner's; other team members see the plan but not the buttons. Each one is offered only when it applies, so an owner sees a few of them at a time.
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Button</th>
                        <th>What it does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Upgrade to Pro</strong></td>
                        <td>Opens the page where you choose a paid plan. A free trial is offered beside the button if this schedule has not used one</td>
                    </tr>
                    <tr>
                        <td><strong>Sell tickets free for {{ (int) config('app.trial_days', 7) }} days</strong></td>
                        <td>Starts the selling trial: priced tickets go on sale with no card and no change to your plan. Offered once, to an owner who has had neither the trial nor a subscription</td>
                    </tr>
                    <tr>
                        <td><strong>Upgrade to Enterprise</strong></td>
                        <td>Moves an active Pro subscription to Enterprise on the same billing term, after a confirmation. The price is beside the button</td>
                    </tr>
                    <tr>
                        <td><strong>Manage Subscription</strong></td>
                        <td>Opens the Stripe billing portal for invoices and card details</td>
                    </tr>
                    <tr>
                        <td><strong>Switch to Yearly</strong> or <strong>Switch to Monthly</strong></td>
                        <td>Changes the billing term. The price is on the button</td>
                    </tr>
                    <tr>
                        <td><strong>Cancel Subscription</strong></td>
                        <td>Opens a short form in place that asks why you are cancelling. Answering is optional. <strong>Keep my subscription</strong> backs out; cancelling leaves your paid features running until the end of the period you have paid for</td>
                    </tr>
                    <tr>
                        <td><strong>Resume Subscription</strong></td>
                        <td>Offered during that period. It undoes the cancellation</td>
                    </tr>
                    <tr>
                        <td><strong>Change to Free Plan</strong></td>
                        <td>For a Pro plan held without a subscription: moves the schedule to Free at once, after a confirmation</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Below the plan, a <strong class="text-gray-900 dark:text-white">Referral Program</strong> card links to your referral dashboard, where referring other organizers earns free months. See <a href="{{ route('marketing.docs.referral_program') }}" class="doc-link">Referral Program</a>.
        </p>

        <h3 id="plan-ads" class="doc-subheading">Ads on free schedules</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Some Event Schedule sites cover their costs by showing ads at the bottom of free schedules' public pages. Where that is switched on, upgrading to Pro removes them, in the same way it removes the "Powered by Event Schedule" credit, and a notice at the top of the Plan tab says so while your pages are showing them. Paid schedules never carry ads.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Even on a site that does show them, ads stay off your embedded calendars, your shareable event graphics, password-protected pages, custom domains, any event page that is actively selling tickets, and any page you or your team members are viewing while signed in.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            You do not have to upgrade to be rid of them. <strong class="text-gray-900 dark:text-white">Do not show other schedules' promotions</strong> under <a href="{{ route('marketing.docs.creating_schedules') }}#settings-advanced" class="doc-link">Settings &rarr; Advanced</a> turns off ads as well as <a href="{{ route('marketing.docs.boost') }}#on-network" class="doc-link">promotions</a>, and it is free on every plan.
        </p>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Not enabled on eventschedule.com</div>
            <p>This is a per-site choice made by whoever runs the Event Schedule installation you are on, and it is off unless they turn it on. eventschedule.com does not show ads on free schedules, so if that is where your schedule lives, none of this applies to you.</p>
        </div>
        @else
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Plan</strong> tab is part of the hosted version of Event Schedule (eventschedule.com), where it shows your subscription, your usage allowances and your billing. A selfhosted install has no subscription: every schedule already has the full Enterprise feature set, with no newsletter or photo caps.
        </p>
        @endif
    </section>

    <!-- Audit Log -->
    <section id="audit-log" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
            </svg>
            Audit Log
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The Audit Log records the significant actions taken on your schedule so you can trace who did what and when. It is most useful on a shared schedule, and after the fact when something has changed and nobody remembers changing it.
        </p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Open it from <strong class="text-gray-900 dark:text-white">Actions &rarr; Audit Log</strong> at the top right of the admin panel. It is a page of its own, newest entry first, with a link above its title that leads back to the schedule. It is available to owners and admins; viewers do not see the menu entry.
        </p>

        <h3 class="doc-subheading">What is recorded</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Entries fall into five categories, in the order the <strong class="text-gray-900 dark:text-white">Category</strong> filter lists them:
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Entries</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Boost</strong></td>
                        <td>Campaign created, paused, resumed, cancelled</td>
                    </tr>
                    <tr>
                        <td><strong>Event</strong></td>
                        <td>Created, updated, deleted, published, accepted, declined</td>
                    </tr>
                    <tr>
                        <td><strong>Sales</strong></td>
                        <td>Checkout, paid, cancelled, refunded, checked in, expired; an installment paid or failed; and, with allocated seating, seats held back, put back on sale, released, moved, or booked at the counter</td>
                    </tr>
                    <tr>
                        <td><strong>Schedule</strong></td>
                        <td>Created, updated, deleted; a member added or removed; a video removed; and an ownership transfer requested, accepted, declined or cancelled</td>
                    </tr>
                    <tr>
                        <td><strong>Subscription</strong></td>
                        <td>Created, changed, cancelled, resumed, and a selling trial started</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Reading and filtering it</h3>
        <ul class="doc-list mb-6">
            <li>Each row gives the <strong>Time</strong>, the <strong>User</strong> who did it (or "System" for anything automatic), the <strong>Action</strong>, and a short line of <strong>Details</strong></li>
            <li>The action is a status mark coloured by what happened: green for something that went through, such as a paid sale or an accepted event; red for something called off or removed; amber for something that needs a look, such as an expired sale or a failed installment</li>
            <li>Sort by any of the four columns by clicking its heading</li>
            <li>Narrow the list by <strong>Category</strong>, by a <strong>From</strong> and <strong>To</strong> date, and by a <strong>Search</strong> across the action and its details, then click <strong>Filter</strong></li>
            <li><strong>Clear</strong> appears once a filter is set and resets them all. Longer logs are paged 50 entries at a time</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The log is scoped to this schedule: its own settings and subscription changes, its events, sales on those events, and its boost campaigns. Sign-ins and account-level changes are not shown here.
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
            <li><x-link href="{{ route('marketing.docs.creating_schedules') }}">Creating Schedules</x-link> - Set up and configure your schedule</li>
            <li><x-link href="{{ route('marketing.docs.creating_events') }}">Creating Events</x-link> - Add and edit events on your schedule</li>
            <li><x-link href="{{ route('marketing.docs.appointments') }}">Appointments</x-link> - Offer bookable time slots on a public booking page</li>
            <li><x-link href="{{ route('marketing.docs.newsletters') }}">Newsletters</x-link> - Email your followers about what is coming up</li>
            <li><x-link href="{{ route('marketing.docs.sharing') }}">Sharing Your Schedule</x-link> - Share your schedule and grow your audience</li>
        </ul>
    </section>

</x-docs-page>
