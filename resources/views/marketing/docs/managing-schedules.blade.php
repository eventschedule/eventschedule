<x-docs-page
    key="managing-schedules"
    description="Learn how to run a schedule day to day in Event Schedule: the calendar, templates, videos, availability, appointments, event requests, followers, your team, your plan, and the audit log."
    lede="Everything on the day-to-day side of a schedule: the calendar and its Actions menu, event requests, followers, team access, your plan, and the audit log."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-group label="Schedule" href="#schedule">
            <x-doc-nav-link href="#actions">Actions</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#templates">Templates</x-doc-nav-link>
        <x-doc-nav-link href="#videos">Videos</x-doc-nav-link>
        <x-doc-nav-link href="#availability">Availability</x-doc-nav-link>
        <x-doc-nav-link href="#appointments">Appointments</x-doc-nav-link>
        <x-doc-nav-link href="#requests">Requests</x-doc-nav-link>
        <x-doc-nav-link href="#followers">Followers</x-doc-nav-link>
        <x-doc-nav-link href="#team">Team</x-doc-nav-link>
        <x-doc-nav-link href="#plan">Plan</x-doc-nav-link>
        <x-doc-nav-link href="#audit-log">Audit Log</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Your schedule's admin panel is the central hub for day-to-day management. It is a row of tabs across the top, each of which is its own page. Not every tab is offered to every schedule: some depend on the <a href="{{ route('marketing.docs.creating_schedules') }}#schedule-types" class="doc-link">schedule type</a>, some on your plan, and one appears only when there is something waiting for you.
        </p>

        <x-doc-screenshot id="managing-schedules--schedule-tab" alt="Schedule admin panel with tabs" loading="eager" />

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
                        <td>Owners and admins. <x-doc-badge plan="pro" /></td>
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
                        <td>Bookable time slots and the bookings they produce</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><a href="{{ route('marketing.docs.allocated_seating') }}" class="doc-link">Seating</a></td>
                        <td>Reusable seating plans for your room, used to sell reserved seats</td>
                        <td><x-doc-badge plan="enterprise" /></td>
                    </tr>
                    <tr>
                        <td><a href="#requests" class="doc-link">Requests</a></td>
                        <td>Submitted events and bookings waiting for your decision</td>
                        <td>Only while something is pending</td>
                    </tr>
                    <tr>
                        <td><a href="#followers" class="doc-link">Followers</a></td>
                        <td>People who follow the schedule, and your QR follow code</td>
                        <td>eventschedule.com only</td>
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
            The <a href="#audit-log" class="doc-link">Audit Log</a> is not a tab. It opens from the <strong class="text-gray-900 dark:text-white">Actions</strong> menu in the top right, which is also where you find importing, embedding, and deleting the schedule.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Your access level changes what you see</div>
            <p>Schedules have three access levels: <strong>owner</strong>, <strong>admin</strong>, and <strong>viewer</strong>. Viewers get a read-only admin panel, and cannot open the schedule settings page at all. Following a schedule is not an access level and grants nothing in the admin panel. See <a href="#team" class="doc-link">Team</a>.</p>
        </div>
    </section>

    <!-- Schedule -->
    <section id="schedule" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Schedule
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Schedule</strong> tab is the main view of your admin panel. It loads one month at a time, so moving between months fetches that month's events.
        </p>

        <ul class="doc-list mb-6">
            <li>Use the <strong>arrow buttons</strong> to step a month back or forward, and <strong>This month</strong> to jump back to today</li>
            <li>Switch between the <strong>calendar</strong> and <strong>list</strong> views with the view toggle; your choice is remembered</li>
            <li><strong>Filters</strong> narrow the view by sub-schedule, category, venue, any dropdown custom field, and toggles for free entry and online events. Only the filters that apply to the events in view are offered. Because one month is loaded at a time, the button is always there even when this particular month has nothing to filter</li>
            <li>Click any <strong>event</strong> to open it, then <strong>Edit</strong> to change it</li>
            <li><strong>Add Event</strong> creates a new event by hand. It appears once the schedule's email address is verified, and is hidden from viewers</li>
            <li><strong>Use a Template</strong> sits next to it once you have at least one saved template (Pro)</li>
            <li>Events with no date at all appear in an <strong>Unscheduled</strong> section below the calendar on venue and talent schedules, each with a <strong>Schedule</strong> button that opens the event so you can give it a date, and a <strong>Decline</strong> button to drop it</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            For details on adding and editing events, see <a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a>.
        </p>

        <h3 id="actions" class="doc-heading text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">Actions Dropdown</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Actions</strong> dropdown sits at the top right of every tab and gathers the operations that act on the schedule as a whole. On narrow screens it also carries <strong>Edit Schedule</strong> and <strong>View Schedule</strong>, which are separate buttons on desktop.
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
                        <td>Opens <a href="{{ route('marketing.docs.ai_import') }}" class="doc-link">AI Import</a> to bulk-import events from text, a file, or a link</td>
                        <td>Owners and admins</td>
                    </tr>
                    <tr>
                        <td><strong>Scan Agenda</strong></td>
                        <td>Reads a printed or photographed agenda and turns it into event parts</td>
                        <td>Owners and admins, on narrow screens. <x-doc-badge plan="enterprise" /></td>
                    </tr>
                    <tr>
                        <td><strong>Sync Events</strong></td>
                        <td>Runs a Google Calendar sync straight away instead of waiting for the next scheduled one</td>
                        <td>Owners and admins, once your Google account is connected and the schedule has a Google Calendar linked</td>
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
        <p class="text-gray-600 dark:text-gray-300 mt-6">
            Scan Agenda and Sync Events only appear when the site has an AI provider configured, and Scan Agenda is a narrow-screen shortcut: on a wide screen you reach the same tool from <strong class="text-gray-900 dark:text-white">Import from Image</strong> in the Agenda section of the event form. If the schedule still has gift cards with a balance left on them, the Delete Schedule confirmation says so before you go ahead.
        </p>
    </section>

    <!-- Templates -->
    <section id="templates" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
            </svg>
            Templates
            <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Save any event as a reusable <strong class="text-gray-900 dark:text-white">template</strong>, then create new events from it in seconds. This suits an event you run again and again on a different date each time. The tab is offered to owners and admins; viewers do not see it.
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open an event and choose <strong>Save as Template</strong> from its Actions menu. The template captures the event's details, tickets, agenda and participants.</li>
            <li>The date is deliberately left blank so you set it fresh each time. A recurring day-of-week pattern is kept.</li>
            <li>On the <strong>Templates</strong> tab, use <strong>Add Event</strong> on a template card to start a new event from it, the pencil icon to rename it, or the bin icon to delete it.</li>
            <li>On the Schedule tab, <strong>Use a Template</strong> next to Add Event opens the same list without leaving the calendar.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The new event opens in the normal event form, prefilled, so nothing is committed until you save it. See <a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a>.
        </p>
        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">On the Free plan</div>
            <p>The Templates tab is still there, but instead of your templates it shows what the feature does and a link to upgrade. Templates you already saved are kept, and reappear if you move back to Pro.</p>
        </div>
    </section>

    <!-- Videos -->
    <section id="videos" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            Videos
        </h2>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400 mb-4">Curator schedules only</span>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Videos</strong> tab helps you put a video on the profile of every act you have booked. It lists the talent appearing on your <strong>upcoming accepted events</strong> who do not have a video yet, and searches YouTube for each one by name automatically as the page loads.
        </p>

        <x-doc-screenshot id="managing-schedules--videos-tab" alt="Videos tab showing YouTube search results" />

        <ul class="doc-list mb-6">
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
            <div class="doc-callout-title">Note</div>
            <p>Viewers can browse the suggestions and watch them, but cannot select, save or skip. The search needs the site to have a Google API key with the YouTube Data API enabled; without one the tab reports that no videos were found.</p>
        </div>
    </section>

    <!-- Availability -->
    <section id="availability" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Availability
        </h2>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 mb-2">Talent schedules only</span>
        <x-doc-badge plan="enterprise" />

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Availability</strong> tab lets each team member mark specific dates as unavailable. It answers "can we book them that night" for the people who run the schedule with you, and it is only on <strong>Talent</strong> schedules.
        </p>

        <x-doc-screenshot id="managing-schedules--availability" alt="Availability calendar" />

        <h3 class="doc-subheading">Setting Availability</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Click a <strong>date</strong> in the calendar to mark it unavailable. It gets a red overlay labelled <strong>Unavailable</strong>.</li>
            <li>Click the same date again to <strong>clear</strong> the marker.</li>
            <li>Click <strong>Save</strong> to store the month's changes. The button stays disabled until you change something, so nothing is saved by accident.</li>
        </ol>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Use the calendar navigation arrows to move between months and mark dates further in advance. Each month is saved as you go, so save before you move on.</p>
        </div>

        <h3 class="doc-subheading">How Team Members See It</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            You edit only your own availability, but everyone's shows up together on the <strong>Schedule</strong> tab:
        </p>
        <ul class="doc-list mb-6">
            <li>Days on which someone is unavailable are highlighted on the Schedule tab calendar</li>
            <li>The info icon on such a day lists <strong>which</strong> team members are unavailable</li>
            <li>Each team member can only edit their <strong>own</strong> dates</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>Availability is <strong>never shown publicly</strong>. It is visible only to signed-in members of the schedule. Marking a day unavailable does not stop anything from being booked on it: it is a note to your team, not a lock. Saving requires the Enterprise plan; on a lower plan the Save button offers an upgrade instead.</p>
        </div>
    </section>

    <!-- Appointments -->
    <section id="appointments" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
            </svg>
            Appointments
            <x-doc-badge plan="free" />
        </h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Appointments</strong> tab is where you offer bookable time slots, Calendly-style. Create appointment types with their own duration, weekly hours and optional price, and guests book a time on your public booking page.
        </p>

        <ul class="doc-list mb-6">
            <li>The tab has two views: <strong>appointment types</strong>, where you set up what can be booked, and <strong>Bookings</strong>, filtered by upcoming, pending, past and cancelled</li>
            <li>A <strong>Your booking page</strong> panel gives you the link to share once a type is active</li>
            <li>The tab header counts bookings that are still waiting for approval, so you can see at a glance if something needs a decision</li>
            <li>Bookings are private, so they never appear on your public schedule, and paid ones show up on your Sales page</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            For the full setup, see the <a href="{{ route('marketing.docs.appointments') }}" class="doc-link">Appointments</a> guide.
        </p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Free plans get one appointment type</div>
            <p>Appointment booking itself is free. On eventschedule.com a free schedule can have <strong>one</strong> active appointment type; Pro removes the cap. Selfhosted installs are unlimited.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Not the same as Availability</div>
            <p>Availability marks whole days your team is not free to be booked for events, and is visible only to your team. Appointments publish specific time slots on a public booking page that anyone can book.</p>
        </div>
    </section>

    <!-- Requests -->
    <section id="requests" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
            </svg>
            Requests
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Requests</strong> tab holds everything waiting for your decision, and it only appears while something is actually pending. Once you have cleared the list the tab disappears again. The tab label carries the count, so you can see how many are waiting without opening it.
        </p>

        <x-doc-screenshot id="managing-schedules--requests-tab" alt="Requests tab showing pending requests" />

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Two different things land here:
        </p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Event requests.</strong> Anyone with your public request link can submit an event to your schedule, if you have turned <strong>Accept requests</strong> on. Each card shows the submitting schedule or venue with its picture, the date, the sub-schedule it was filed under, and the answers to any questions you added to your request form.</li>
            <li><strong class="text-gray-900 dark:text-white">Appointment bookings</strong>, when the appointment type has <strong>Require approval before confirming</strong> turned on. These cards are badged with the appointment type and show the guest's name, email, phone, chosen time, price and payment status, plus any note they left. A booking a guest has moved to a new time is badged as moved, so you can spot it in a long list.</li>
        </ul>

        <h3 class="doc-subheading">Working through the list</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong>View</strong> opens the public page for an event request so you can see the whole thing, and <strong>Edit</strong> opens it in the event form if you want to tidy it up before publishing.</li>
            <li><strong>Accept</strong> publishes the event on your schedule, or confirms the booking. The person who submitted it is emailed, and a guest who booked an appointment gets their confirmation and calendar invite.</li>
            <li><strong>Decline</strong> asks you to confirm, then removes it from your schedule and emails the submitter. Declining a booking also cancels it and frees the slot; if it was already paid you are reminded to refund it yourself.</li>
            <li><strong>Accept All</strong> at the top of the list takes everything in one go, after a confirmation that names the count. There is no bulk decline: declining is one at a time, on purpose.</li>
        </ol>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Who can act on requests</div>
            <p>Only owners and admins get the Accept, Decline and Accept All buttons. Viewers see the same cards, and can open a request, but cannot decide it. An appointment whose start time has already passed is refused rather than confirmed, and Accept All steps over it instead of failing.</p>
        </div>

        <h3 class="doc-subheading">Being told about them</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Event Schedule emails owners and admins when new requests arrive. The setting is <strong class="text-gray-900 dark:text-white">New event requests</strong> under <a href="{{ route('marketing.docs.creating_schedules') }}#settings-notifications" class="doc-link">Settings &rarr; Notifications</a>, it is on unless you turn it off, and it is per person rather than per schedule. Viewers are never notified. If the schedule does not require approval there is nothing to notify about, and no email is sent.
        </p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            To turn requests on, choose your request form, name schedules whose submissions skip approval, and set your request terms, see <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-requests" class="doc-link">Creating Schedules: Requests</a>. Talent schedules get a shorter version of those settings, because a request to book a performer is always reviewed by hand.
        </p>
    </section>


    <!-- Followers -->
    <section id="followers" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            Followers
        </h2>
        @if(config('app.hosted'))
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Followers</strong> tab lists the people who have followed your schedule from its public page. Followers are the default recipients when you send a <a href="{{ route('marketing.docs.newsletters') }}#recipients" class="doc-link">newsletter</a>, which is the only thing that emails them: adding an event does not notify anyone by itself.
        </p>

        <ul class="doc-list mb-6">
            <li>The table gives each follower's <strong>name</strong>, <strong>email address</strong>, their own schedule if they run one, and the <strong>date</strong> they followed you</li>
            <li>Sort by name, email or date by clicking the column heading, and page through longer lists at the bottom</li>
            <li><strong>QR Code</strong> at the top right downloads a PNG that points at your public schedule page, or at your custom domain if you have one, ready to print on a poster or a flyer</li>
            <li>Before you have any followers, the tab shows your public link instead so you can copy and share it</li>
        </ul>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Follower details stay private</div>
            <p>Names and email addresses are visible only to the schedule's own members, here and on the newsletter pages. They never appear on your public schedule, your embedded calendar, or your public stats. Anyone clicking Follow is told first that you will be able to see their name and email.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The QR code and the follow link are free on every plan. For more on growing your audience, see <a href="{{ route('marketing.docs.sharing') }}#followers" class="doc-link">Sharing: Followers</a>.
        </p>
        @else
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Followers</strong> tab is part of the hosted version of Event Schedule (eventschedule.com). It lists the people who follow your schedule with their name, email address and the date they followed, and offers a QR code pointing at your public page.
        </p>
        @endif
    </section>

    <!-- Team -->
    <section id="team" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
            Team
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Team</strong> tab lists everyone who can get into this schedule's admin panel and at what level. Every schedule has exactly one owner: the person who created it.
        </p>

        <x-doc-screenshot id="managing-schedules--team-tab" alt="Team management tab" />

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
                        <td>Everything an admin can, plus change a member's level, remove members, manage the plan and billing, and delete the schedule</td>
                        <td>Be removed or demoted</td>
                    </tr>
                    <tr>
                        <td><strong>Admin</strong></td>
                        <td>Run the schedule day to day: add and edit events, accept and decline requests, edit the schedule settings, sell tickets, invite new members</td>
                        <td>Change anyone's level, remove another member, or delete the schedule</td>
                    </tr>
                    <tr>
                        <td><strong>Viewer</strong></td>
                        <td>Read the admin panel: browse the calendar, requests, followers, team and bookings, generate a graphic, grab the embed code</td>
                        <td>Add or change anything, act on requests, or open the schedule settings page at all</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mt-6 mb-4">
            <strong class="text-gray-900 dark:text-white">Following is not an access level.</strong> Someone who follows your schedule from its public page is a member of your audience, not of your team, and gets no admin panel access at all.
        </p>

        <h3 class="doc-subheading">Managing Members</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Click <strong>Add Member</strong> and give their name and email address. A phone number is optional, and the level defaults to Admin.</li>
            <li>They are emailed an invitation. If they have not accepted yet, the level column shows a <strong>Resend Invite</strong> button in place of a level, and, on eventschedule.com, a second <strong>SMS</strong> button when you gave a phone number and text messaging is configured.</li>
            <li>Once they have signed up, the owner can change their level between <strong>Admin</strong> and <strong>Viewer</strong> from the dropdown in that row. It saves as soon as you pick.</li>
            <li><strong>Remove</strong> revokes access. Only the owner can remove someone else; anyone can remove themselves. The owner's own row has no Remove button.</li>
            <li>Sort the list by name or email by clicking the column heading.</li>
        </ol>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Team size by plan</div>
            <p>On the <strong>Free</strong> and <strong>Pro</strong> plans a schedule has a single member: you. Adding anyone else needs the <strong>Enterprise</strong> plan, where the Add Member button becomes active. On eventschedule.com a team is capped at <strong>5 members</strong> in total. Selfhosted installs count as Enterprise, so the button is available there without a subscription.</p>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">If the plan lapses</div>
            <p>On eventschedule.com, invited members can only open the admin panel while the schedule is on Enterprise. If it drops to a lower plan they are turned away with a message asking the owner to upgrade, and the owner keeps full access on their own. Nobody is removed, so restoring Enterprise restores their access.</p>
        </div>

        <h3 id="transfer-ownership" class="doc-subheading">Transferring Ownership</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Ownership can be handed to another account: a venue changes hands, an organizer leaves, or you set a schedule up for someone and want it to be theirs. It is available on every plan, and only the owner can start it.
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>On the Team tab click <strong>Transfer ownership</strong> and enter the new owner's email address.</li>
            <li>On <strong>Enterprise</strong> and selfhosted installs you can turn off <strong>Remove me from this schedule</strong> to stay on as an admin afterwards. Free and Pro schedules hold a single member, so there you are always removed.</li>
            <li>They are emailed a link. Nothing moves yet: the request sits on the Team tab with <strong>Resend Invite</strong> and <strong>Cancel</strong> buttons, and expires after seven days.</li>
            <li>To accept, they sign in with the address you sent it to. The link on its own is not enough, and if they do not have an account yet they can create one with that address.</li>
            <li>As soon as they accept, the schedule is theirs: every event, follower, image and setting comes with it.</li>
        </ol>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">What changes for the previous owner</div>
            <p>Ticket payments for this schedule's events start settling into the new owner's payment account, so the new owner should check their payment settings before the next sale. The previous owner's calendar sync for the schedule is disconnected, and their events, followers and settings all move across. Events curated in from other schedules are untouched: those still belong to whoever created them.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Billing on eventschedule.com</div>
            <p>The previous owner is never charged for the schedule again: their subscription is cancelled at the end of the billing period already paid for and their saved card is removed. The schedule keeps its plan until that period ends. Before then the new owner adds their own billing details to keep it, otherwise the schedule moves to the free plan. Selfhosted installs have no billing step at all.</p>
        </div>
    </section>

    <!-- Plan -->
    <section id="plan" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
            </svg>
            Plan
        </h2>
        @if(config('app.hosted'))
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Plan</strong> tab shows this schedule's subscription and what you have used of it. Plans are per schedule, so each schedule you run has its own.
        </p>

        <h3 class="doc-subheading">What it shows</h3>
        <ul class="doc-list mb-6">
            <li><strong>Current plan</strong> (Free, Pro or Enterprise) and the billing term, monthly or yearly</li>
            <li><strong>Status</strong>: Trial, Active, Cancelled, Past due or Inactive, with the date your trial ends, your access ends, or the subscription renews</li>
            <li>The <strong>payment method</strong> on file, with a prompt to add one if there is none</li>
            <li>Three usage meters: <strong>ticket sales</strong> this month, <strong>newsletter emails</strong> this month, and <strong>photo</strong> storage, each showing what is used, what is left, and when it resets</li>
        </ul>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">The newsletter meter counts recipients</div>
            <p>Each recipient counts as one email, so sending a single newsletter to 100 followers uses 100 of the allowance. Free schedules get 10 a month, Pro 100, Enterprise 1,000. Selfhosted installs are unlimited.</p>
        </div>

        <h3 class="doc-subheading">What you can do</h3>
        <ul class="doc-list mb-6">
            <li><strong>Upgrade</strong> to Pro or Enterprise. A free trial is offered alongside the upgrade button if this schedule has not used one</li>
            <li><strong>Manage subscription</strong> opens the Stripe billing portal for invoices and card details</li>
            <li>Switch between <strong>monthly and yearly</strong> billing, with the price for each shown on the button</li>
            <li><strong>Cancel</strong>, which leaves your paid features running until the end of the period, and <strong>Resume</strong> during that grace period</li>
            <li><strong>Change to the free plan</strong> outright</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            These controls are the schedule owner's; other team members see the plan but not the buttons. The tab also links to the referral program, which can offset what you pay.
        </p>

        <h3 class="doc-subheading">Ads on free schedules</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Some Event Schedule sites cover their costs by showing ads at the bottom of free schedules' public pages. Where that is switched on, upgrading to Pro removes them, in the same way it removes the "Powered by Event Schedule" credit. Paid schedules never carry ads.
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
            The <strong class="text-gray-900 dark:text-white">Plan</strong> tab is part of the hosted version of Event Schedule (eventschedule.com), where it shows your subscription, your usage allowances and your billing. A selfhosted install has no subscription: every schedule already has the full Enterprise feature set, with no ticket, newsletter or photo caps.
        </p>
        @endif
    </section>

    <!-- Audit Log -->
    <section id="audit-log" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
            </svg>
            Audit Log
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The Audit Log records the significant actions taken on your schedule so you can trace who did what and when. It is most useful on a shared schedule, and after the fact when something has changed and nobody remembers changing it.
        </p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Open it from <strong class="text-gray-900 dark:text-white">Actions &rarr; Audit Log</strong> at the top right of the admin panel. It is available to owners and admins; viewers do not see the menu entry.
        </p>

        <h3 class="doc-subheading">What is recorded</h3>
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
                        <td><strong>Event</strong></td>
                        <td>Created, updated, deleted, published, accepted, declined</td>
                    </tr>
                    <tr>
                        <td><strong>Sale</strong></td>
                        <td>Checkout, paid, cancelled, refunded, checked in, expired</td>
                    </tr>
                    <tr>
                        <td><strong>Schedule</strong></td>
                        <td>Created, updated, deleted, team member added, team member removed</td>
                    </tr>
                    <tr>
                        <td><strong>Subscription</strong></td>
                        <td>Started, changed, cancelled, resumed</td>
                    </tr>
                    <tr>
                        <td><strong>Boost</strong></td>
                        <td>Campaign created, paused, resumed, cancelled</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Reading and filtering it</h3>
        <ul class="doc-list mb-6">
            <li>Each row gives the <strong>time</strong>, the <strong>person</strong> who did it (or "System" for anything automatic), the <strong>action</strong> as a coloured label, and a short <strong>detail</strong></li>
            <li>Sort by any of those four columns by clicking its heading</li>
            <li>Filter by <strong>category</strong>, by a <strong>from</strong> and <strong>to</strong> date, and by a free-text <strong>search</strong> across the action and its details</li>
            <li><strong>Clear</strong> resets the filters, and longer logs are paged 50 entries at a time</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The log is scoped to this schedule: its own settings and subscription changes, its events, sales on those events, and its boost campaigns. Sign-ins and account-level changes are not shown here.
        </p>
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
            <li><x-link href="{{ route('marketing.docs.creating_schedules') }}">Creating Schedules</x-link> - Set up and configure your schedule</li>
            <li><x-link href="{{ route('marketing.docs.creating_events') }}">Creating Events</x-link> - Add and edit events on your schedule</li>
            <li><x-link href="{{ route('marketing.docs.appointments') }}">Appointments</x-link> - Offer bookable time slots on a public booking page</li>
            <li><x-link href="{{ route('marketing.docs.newsletters') }}">Newsletters</x-link> - Email your followers about what is coming up</li>
            <li><x-link href="{{ route('marketing.docs.sharing') }}">Sharing Your Schedule</x-link> - Share your schedule and grow your audience</li>
        </ul>
    </section>

</x-docs-page>
