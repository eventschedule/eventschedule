<x-docs-page
    key="creating-schedules"
    title="Creating Schedules: Settings, Links, Sync - Event Schedule"
    description="Configure a schedule in Event Schedule: details, address, short links, sub-schedules, notifications, event requests, event sources and calendar sync."
    lede="Set up and configure your schedule - from basic details and contact info to short links, sub-schedules, notifications, event requests and calendar sync."
    article-description="Configure a schedule in Event Schedule: details, address, contact info, short links, sub-schedules, settings and notifications, event requests, event sources, selfhost auto import and calendar integrations."
>
    <x-slot:toc>
        {{-- The schedule form's own order: its sections down the sidebar, and each section's rows. --}}
        <x-doc-nav-link href="#schedule-form">The Schedule Form</x-doc-nav-link>
        <x-doc-nav-link href="#schedule-types">Schedule Types</x-doc-nav-link>
        <x-doc-nav-group label="Details" href="#details">
            <x-doc-nav-link href="#details-general">General</x-doc-nav-link>
            <x-doc-nav-link href="#ai-details-generator">AI Details Generator</x-doc-nav-link>
            <x-doc-nav-link href="#details-localization">Language and time</x-doc-nav-link>
            <x-doc-nav-link href="#contact-info">Contact Info</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#event-sources">Event Sources</x-doc-nav-link>
        <x-doc-nav-link href="#address">Address</x-doc-nav-link>
        <x-doc-nav-link href="#merge">Merging Duplicates</x-doc-nav-link>
        <x-doc-nav-link href="#style">Style</x-doc-nav-link>
        <x-doc-nav-link href="#gallery">Gallery</x-doc-nav-link>
        <x-doc-nav-link href="#videos-links">Videos & Links</x-doc-nav-link>
        <x-doc-nav-group label="Customize" href="#customize">
            <x-doc-nav-link href="#customize-subschedules">Sub-schedules</x-doc-nav-link>
            <x-doc-nav-link href="#customize-custom-fields">Custom Fields</x-doc-nav-link>
            <x-doc-nav-link href="#customize-categories">Categories</x-doc-nav-link>
            <x-doc-nav-link href="#customize-custom-labels">Custom Labels</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Settings" href="#settings">
            <x-doc-nav-link href="#settings-general">Schedule URL</x-doc-nav-link>
            <x-doc-nav-link href="#custom-domain">Custom Domain</x-doc-nav-link>
            <x-doc-nav-link href="#settings-notifications">Notifications</x-doc-nav-link>
            <x-doc-nav-link href="#settings-advanced">Advanced</x-doc-nav-link>
            <x-doc-nav-link href="#url-pattern-variables">URL Pattern Variables</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Engagement" href="#engagement">
            <x-doc-nav-link href="#engagement-requests">Requests</x-doc-nav-link>
            <x-doc-nav-link href="#engagement-fan-content">Fan Content</x-doc-nav-link>
            <x-doc-nav-link href="#engagement-feedback">Feedback</x-doc-nav-link>
            <x-doc-nav-link href="#engagement-carpool">Carpool</x-doc-nav-link>
            <x-doc-nav-link href="#engagement-sponsors">Sponsors</x-doc-nav-link>
            <x-doc-nav-link href="#engagement-venue-map">Venue map</x-doc-nav-link>
            <x-doc-nav-link href="#engagement-accommodation">Accommodation</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#gift-cards">Gift Cards</x-doc-nav-link>
        <x-doc-nav-link href="#auto-import">Auto Import</x-doc-nav-link>
        <x-doc-nav-group label="Integrations" href="#integrations">
            <x-doc-nav-link href="#integrations-email">Email Settings</x-doc-nav-link>
            <x-doc-nav-link href="#calendar-sync">Calendar sync</x-doc-nav-link>
            <x-doc-nav-link href="#integrations-google">Google Calendar</x-doc-nav-link>
            <x-doc-nav-link href="#integrations-microsoft">Outlook Calendar</x-doc-nav-link>
            <x-doc-nav-link href="#integrations-caldav">CalDAV Calendar</x-doc-nav-link>
            <x-doc-nav-link href="#integrations-feeds">Feeds from other sites</x-doc-nav-link>
            <x-doc-nav-link href="#integrations-advanced">Calendar text and feeds</x-doc-nav-link>
            <x-doc-nav-link href="#available-variables">Available Variables</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    {{-- The page as a whole: where the form is, what its sections are, how saving works. --}}
    <section id="schedule-form" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
            The Schedule Form
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A new schedule is created with a name and nothing else (see <a href="{{ route('marketing.docs.getting_started') }}#create-schedule" class="doc-link">Getting Started</a>). Everything else about it is set on one page, the schedule form, and this guide follows that form from top to bottom.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">To open it, choose the schedule in the sidebar and click <strong class="text-gray-900 dark:text-white">Edit Schedule</strong> at the top of its page. On a phone the same entry is in the <strong class="text-gray-900 dark:text-white">Actions</strong> menu. The schedule's owner and its admins can open the form; a team member with view access cannot.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The form's sections are listed down the left, and on a phone they are stacked, each opening when you tap it. Under a section's name is one line saying what it holds now, and a dot appears beside the name once you have changed something in it. Inside a section, what everyone fills in is on the page as it opens. The rest sits in rows: a row shows its name and its current setting, opens in place when you click it, and one row of a section is open at a time.</p>

        <x-doc-screenshot id="creating-schedules--section-details" alt="The schedule form: the sections down the left with a line under each name, the Details section open on the schedule's name and description, and the bar with Cancel and Save at the bottom" loading="eager" />

        <p class="text-gray-600 dark:text-gray-300 mb-4">Not every section is offered to every schedule. In the form's own order:</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>What it holds</th>
                        <th>Shown for</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><a href="#details" class="doc-link">Details</a></td>
                        <td>Name, description and announcement bar, with rows for Language and time and for Contact Info</td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><a href="#event-sources" class="doc-link">Event Sources</a></td>
                        <td>Talent and venue schedules whose events appear on yours automatically</td>
                        <td>Curator schedules</td>
                    </tr>
                    <tr>
                        <td><a href="#address" class="doc-link">Address</a></td>
                        <td>The venue's street address</td>
                        <td>Venue schedules</td>
                    </tr>
                    <tr>
                        <td><a href="#merge" class="doc-link">Merge Venue</a></td>
                        <td>Folding a duplicate venue into another one you manage</td>
                        <td>An <a href="{{ route('marketing.docs.creating_events') }}#claim" class="doc-link">unclaimed</a> Venue schedule, when you manage another venue it could be merged into</td>
                    </tr>
                    <tr>
                        <td><a href="#style" class="doc-link">Style</a></td>
                        <td>Profile image, accent color and font, with rows for Event animation, Background, and Header and layout</td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><a href="#gallery" class="doc-link">Gallery</a> <x-doc-badge plan="pro" /></td>
                        <td>Photos shown on your schedule page</td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><a href="#videos-links" class="doc-link">Videos &amp; Links</a></td>
                        <td>YouTube videos and social links, with a short link for each</td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><a href="#customize" class="doc-link">Customize</a></td>
                        <td>Rows for Sub-schedules, Custom Fields, Categories and Custom Labels</td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><a href="#settings" class="doc-link">Settings</a></td>
                        <td>The Schedule URL, with rows for Notifications and Advanced</td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><a href="#engagement" class="doc-link">Engagement</a></td>
                        <td>Rows for Requests, Fan Content, Feedback, Carpool, Sponsors and Accommodation</td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><a href="#gift-cards" class="doc-link">Gift Cards</a> <x-doc-badge plan="pro" /></td>
                        <td>Selling gift cards that are redeemed for tickets</td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><a href="#auto-import" class="doc-link">Auto Import</a></td>
                        <td>Web pages to read events from once a day</td>
                        <td>Selfhosted installs</td>
                    </tr>
                    <tr>
                        <td><a href="#integrations" class="doc-link">Integrations</a></td>
                        <td>Rows for Email Settings (hosted platform only), Google Calendar, Outlook Calendar, CalDAV Calendar, and Calendar text and feeds</td>
                        <td>Every schedule</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">What needs a plan</div>
            <p>The form itself, and most of what is on it, is free. <x-doc-badge plan="pro" /> covers the announcement bar, Gallery, Custom Fields, Custom Labels, Feedback, Carpool, Sponsors, Gift Cards and custom CSS. <x-doc-badge plan="enterprise" /> covers the two AI generators, a custom domain, and Internal or Unlisted as the default visibility. A row your plan does not include carries a lock and says which plan it needs when you open it. A <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install resolves to Enterprise, so nothing on the form is held back there.</p>
        </div>

        <h3 id="saving" class="doc-subheading">Saving</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">There is one <strong class="text-gray-900 dark:text-white">Save</strong> for the whole form, in the bar at the bottom of the page. It saves every section at once, whichever one you are looking at. The same bar says where things stand:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Unsaved</strong>, followed by the sections you have changed. Each name is a link back to that section.</li>
            <li><strong class="text-gray-900 dark:text-white">Saving removes</strong>, followed by what you took off a list, by name: a sub-schedule, a custom field, a category, a custom label, a sponsor, a source schedule. It is said before the save that removes it.</li>
            <li><strong class="text-gray-900 dark:text-white">Check</strong>, followed by the sections to look at, when a save was refused. The form opens the section and the row holding the first message, and keeps what you typed.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300"><strong class="text-gray-900 dark:text-white">Cancel</strong>, beside Save, leaves the form. If anything is unsaved it asks first: <strong class="text-gray-900 dark:text-white">Keep editing</strong> or <strong class="text-gray-900 dark:text-white">Discard</strong>.</p>
    </section>

    <!-- Schedule Types -->
    <section id="schedule-types" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            Schedule Types
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Every schedule is one of three types. The type is chosen when the schedule is created and cannot be changed afterwards:</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Best For</th>
                        <th>What the form adds or leaves out</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Talent</span></td>
                        <td>Musicians, DJs, performers, speakers</td>
                        <td>A shorter <a href="#engagement-requests" class="doc-link">Requests</a> row, because a request to book a performer is always read by hand. <strong class="text-gray-900 dark:text-white">Country</strong> in <a href="#contact-info" class="doc-link">Contact Info</a>. No <strong class="text-gray-900 dark:text-white">Hide Videos</strong> switch.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Venue</span></td>
                        <td>Bars, clubs, theaters, event spaces</td>
                        <td>The <a href="#address" class="doc-link">Address</a> section, and <a href="#merge" class="doc-link">Merge Venue</a> on a venue nobody has claimed.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Curator</span></td>
                        <td>Promoters, bloggers, community organizers</td>
                        <td>The <a href="#event-sources" class="doc-link">Event Sources</a> section, second in the list. <strong class="text-gray-900 dark:text-white">City</strong> and <strong class="text-gray-900 dark:text-white">Country</strong> in Contact Info. <strong class="text-gray-900 dark:text-white">Require Account</strong> starts switched on.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mt-6">The type also decides which tabs a schedule's own page has (Availability for a Talent, Seating plans for a Venue, Videos for a Curator): see <a href="{{ route('marketing.docs.getting_started') }}#schedule-types" class="doc-link">choosing a type</a> and <a href="{{ route('marketing.docs.managing_schedules') }}" class="doc-link">Managing Schedules</a>.</p>
    </section>

    <!-- Details -->
    <section id="details" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            Details
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The <strong class="text-gray-900 dark:text-white">Details</strong> section says who the schedule is. The name, the descriptions and the announcement bar are on the page as it opens. <strong class="text-gray-900 dark:text-white">Language and time</strong> and <strong class="text-gray-900 dark:text-white">Contact Info</strong> are two rows under them, each saying its current setting on the row.</p>

        <!-- General Tab -->
        <h3 id="details-general" class="doc-subheading">General</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule Name</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Required. Your schedule's display name, shown at the top of your schedule page and in search results. Use your band name, venue name, or organization name.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Short Description</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">One line, up to 200 characters. It is shown under your schedule's name when the <a href="{{ route('marketing.docs.schedule_styling') }}#header-style" class="doc-link">header style</a> is Banner, and it opens the description that search engines and link previews show for your page.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Description</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A bio or description of your schedule, shown at the top of your schedule page. Supports <strong class="text-gray-900 dark:text-white">Markdown formatting</strong> for links, bold text, lists, and more. Tell visitors what you're about and what kind of events they can expect.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Translated name and description</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Once <a href="#details-localization" class="doc-link">a second language</a> is switched on and a translation exists, an extra field appears under the name, the short description and the banner message so you can correct the wording by hand. Each is labelled with the target language, for example <strong class="text-gray-900 dark:text-white">Name (English)</strong>, so it follows whichever language you chose to translate into rather than always being English.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Show an announcement bar <x-doc-badge plan="pro" /></h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Turn on <strong class="text-gray-900 dark:text-white">Show an announcement bar</strong> to display a message in a bar at the top of your schedule's guest page, such as a venue change or a "tickets on sale" notice. The <strong class="text-gray-900 dark:text-white">Banner message</strong> box takes up to 500 characters and accepts Markdown, including links. <strong class="text-gray-900 dark:text-white">Show on event pages too</strong> extends the banner from the schedule page to individual event pages. Below Pro the switch is shown greyed out, with a note saying so.</p>
            </div>
        </div>

        <!-- AI Details Generator -->
        <h3 id="ai-details-generator" class="doc-subheading">AI Details Generator <x-doc-badge plan="enterprise" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Let AI write your schedule's <strong class="text-gray-900 dark:text-white">short description</strong> and <strong class="text-gray-900 dark:text-white">description</strong> from its name and type. Those two fields are all it generates.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Click <strong class="text-gray-900 dark:text-white">AI Generator</strong>, at the end of the Details section's title row.</li>
            <li>Tick the fields you want. A field that already has content is left unticked and marked with a blue dot; tick it anyway and a warning tells you its content will be replaced.</li>
            <li>Optionally add <strong class="text-gray-900 dark:text-white">Additional instructions</strong> (up to 500 characters) to steer the tone, and tick <strong class="text-gray-900 dark:text-white">Save as default for this schedule</strong> to reuse them next time.</li>
            <li>Optionally open <strong class="text-gray-900 dark:text-white">View/edit AI prompt</strong> to see the prompt that will be sent and adjust it for this run.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Generate</strong> and review the preview. <strong class="text-gray-900 dark:text-white">Regenerate</strong> redoes one item, <strong class="text-gray-900 dark:text-white">Accept</strong> writes the results into the form, and <strong class="text-gray-900 dark:text-white">Discard</strong> drops them. Nothing is saved until you save the schedule.</li>
        </ol>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Requirements</div>
            <p>The button only appears when the site has an AI key configured. Selfhosted installations need a <x-link href="https://ai.google.dev/" target="_blank">Gemini API key</x-link> in the environment settings, because this feature is generated by Gemini. On the hosted platform a schedule below Enterprise sees the button greyed out, and there is a daily cap on AI text generation per schedule; you are told if you reach it.</p>
        </div>

        <!-- Localization Tab -->
        <h3 id="details-localization" class="doc-subheading">Language and time</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Language</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The language you write your schedule and events in, which also sets the interface language on your schedule page. Twelve languages are supported: Arabic, Dutch, English, Estonian, French, German, Hebrew, Italian, Portuguese, Romanian, Russian, and Spanish.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Offer a second language to visitors</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Turn this on to have your schedule and events translated automatically, then pick the target under <strong class="text-gray-900 dark:text-white">Translate into</strong> (English by default; the language you write in is not offered). Visitors get a button to switch between the two. Available on all plans. Translations are generated in the background, so allow up to an hour for them to appear; changing the target re-translates everything, and editing an event refreshes its own translation. Turning the setting back off discards every stored translation for the schedule, including wording you corrected by hand, so you are asked to confirm first.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Timezone</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Set your schedule's timezone. Event times are entered and displayed in this timezone, so set it before you add events. It matters most when your audience is spread across regions.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Use 24-hour time format</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Off shows times as 2:00 PM, on shows them as 14:00. The choice carries through to event pages, calendar descriptions and the <code class="doc-inline-code">{time}</code> template variable.</p>
            </div>
        </div>

        <!-- Contact Info -->
        <h3 id="contact-info" class="doc-subheading">Contact Info</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The second row of Details holds how to reach you. Closed, the row shows the schedule's email address. These details appear on your public schedule page, the email address and the phone number only when you switch them on.</p>

        <x-doc-screenshot id="creating-schedules--section-contact-info" alt="The Contact Info row of the Details section, open: Email, Show email address, Phone Number, Website, City and Country" />

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Email</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Required. This is the schedule's own address. On the hosted platform it has to be verified before the schedule's guest page can be opened, and changing it asks for verification again; a selfhosted install treats it as verified. Turn on <strong class="text-gray-900 dark:text-white">Show email address</strong> to publish it to visitors as well; leave it off and the address stays private to you. It is not where your <a href="#settings-notifications" class="doc-link">notifications</a> go: those are sent to the email address on your own account, and to the schedule's <a href="#notification-email" class="doc-link">shared notification address</a> if you add one.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Phone Number</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A contact number, entered with a country picker. Once a number is saved a <strong class="text-gray-900 dark:text-white">Show phone number</strong> toggle appears next to it. On the hosted platform the number must be verified by SMS code before it is shown publicly.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Website</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Link to your main website. It opens in a new tab when visitors click it.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">City and Country</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A way to say roughly where you are without a street address. <strong class="text-gray-900 dark:text-white">Curator</strong> schedules get both City and Country; <strong class="text-gray-900 dark:text-white">Talent</strong> schedules get Country only. Venue schedules use the <a href="#address" class="doc-link">Address</a> section instead, so neither field appears here for them.</p>
            </div>
        </div>
    </section>

    <!-- Event Sources -->
    <section id="event-sources" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
            </svg>
            Event Sources <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 ml-2">Curator</span>
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">On a <strong class="text-gray-900 dark:text-white">Curator</strong> schedule, Event Sources is the second section of the form. List the talent and venue schedules you want to follow and everything they publish shows up on your calendar on its own. It covers what they have already run as well as what is coming, and each new event appears within a few minutes of going live. This is the fastest way to stand up a city guide or a festival hub: pick your rooms and your acts once, and stop copying listings by hand. It is free on every plan.</p>

        <x-doc-screenshot id="creating-schedules--section-sources" alt="The Event Sources section of a curator's schedule form: Add a source schedule, and under Suggested a button for each schedule it already shares events with" />

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Schedules</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Search by name or subdomain and pick the talent or venue schedule you want. Only talent and venue schedules can be a source, so one curator never chains onto another.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Anything the schedule has chosen not to publish stays private: drafts, internal events, unlisted events and anything it has not accepted are all left out.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Each saved source shows how many of its events are on your calendar right now, past and upcoming, so you can tell at a glance whether it is feeding your schedule. An event you removed by hand is left out of that number, and an event two sources share counts under both, so the numbers need not add up to your total.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sub-schedule</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Optional. File everything from one source under a <a href="#customize-subschedules" class="doc-link">sub-schedule</a> so visitors can filter by it. Changing it moves that source's existing events too.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Suggested</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Schedules you already share events with, offered as one-click shortcuts.</p>
            </div>
        </div>

        <h3 class="doc-subheading">Setting up event sources</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Edit Schedule</strong> on a curator schedule and choose <strong class="text-gray-900 dark:text-white">Event Sources</strong>.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">+ Add a source schedule</strong> and search for the talent or venue you want to follow, or click one of the names under <strong class="text-gray-900 dark:text-white">Suggested</strong>.</li>
            <li>Optionally choose a sub-schedule to file that source's events under. The choice is offered once the schedule has sub-schedules.</li>
            <li>Save. Their events, past and upcoming, appear on your calendar right away.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6">To stop following a schedule, click the <strong class="text-gray-900 dark:text-white">&times;</strong> beside it. The bar at the bottom names it after <strong class="text-gray-900 dark:text-white">Saving removes</strong>, and saving takes its events off your calendar.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Sourced events count as yours almost everywhere: they show on your calendar and public page, in your <a href="{{ route('marketing.docs.event_graphics') }}" class="doc-link">event graphics</a>, and in your iCal and RSS feeds. The exception is the automatic digest to your <a href="#settings-notifications" class="doc-link">email subscribers</a>, which only covers events this schedule created itself. To drop a single one, open it and choose Remove from schedule; it stays gone even though the source is still connected. Removing the source itself takes its events with it and leaves anything you added by hand untouched.</p>
        </div>
    </section>

    <!-- Address -->
    <section id="address" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
            Address
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The <strong class="text-gray-900 dark:text-white">Address</strong> section appears on <strong class="text-gray-900 dark:text-white">Venue</strong> schedules only. The address is public. Your schedule page shows it as a link that opens Google Maps, your event pages show it on the venue card, with a map where the site has a Google Maps key, and it is what the <a href="#engagement-accommodation" class="doc-link">nearby accommodation</a> map centres on. Talent and Curator schedules say where they are in the <a href="#contact-info" class="doc-link">Contact Info</a> row instead.</p>

        <x-doc-screenshot id="creating-schedules--section-address" alt="The Address section of a venue's schedule form: Street Address, City, State / Province, Postal Code and Country, with View Map and Validate Address under them" />

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Street Address</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Required. Your venue's street address, for example "123 Main Street".</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">City, State / Province, Postal Code</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Three separate fields. All are optional, but the more you give the more precisely the address can be placed on a map.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Country</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Select your country from the dropdown. It is used for address formatting and map display.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">View Map and Validate Address</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">View Map</strong> opens what you have typed in Google Maps, in a new tab, so you can check it landed in the right place. <strong class="text-gray-900 dark:text-white">Validate Address</strong> looks the address up and offers a tidied version of each field; click <strong class="text-gray-900 dark:text-white">Accept</strong> to take it. Validate Address only appears when the site has a Google Maps key configured.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">The address is looked up when you save</div>
            <p>Saving a new or changed address looks up its coordinates as part of the save. That lookup is what puts the map on your event pages and places the accommodation map. Clearing the address clears the coordinates again. The lookup needs a Google Maps key on the site, so a selfhosted install without one keeps the address as text and shows no map.</p>
        </div>
    </section>

    <!-- Merge -->
    <section id="merge" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
            </svg>
            Merging duplicates
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Importing events tends to create the same venue twice, once as "The Anchor" and again as "Anchor Bar". Rather than leave your calendar pointing at two half-empty pages, merge them. Every event moves to the schedule you keep and the duplicate goes away.</p>

        <h3 class="doc-subheading">Merge Venue</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A <strong class="text-gray-900 dark:text-white">Merge Venue</strong> section appears on the schedule form of an unclaimed Venue schedule when you manage at least one other venue. It sits after Address in the list. The <strong class="text-gray-900 dark:text-white">Merge into</strong> dropdown lists the other venues you manage; pick one and click the red <strong class="text-gray-900 dark:text-white">Merge Venue</strong> button, which asks you to confirm and says how many events will move. All of this schedule's events move to the target and this one is removed. The merge happens when you confirm: it does not wait for the form's Save. If Event Schedule spots a likely match by name, city and country, it names it for you above the dropdown, so usually you only have to confirm.</p>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Only unclaimed schedules can be merged</div>
            <p>Merging is offered for schedules nobody has claimed yet, which is exactly the kind an import creates - the pages described under <a href="{{ route('marketing.docs.creating_events') }}#claim" class="doc-link">Pages Created for Others</a>. Once someone claims a schedule it has a real operator behind it, so it can no longer be absorbed into another. Both schedules must also be the same type, so a venue merges into a venue and never into a talent.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">If some events already exist on the target, you are told how many before you commit. Those are not duplicated: where the same event sits on both schedules, the two entries are combined and any detail the target is missing is filled in from the schedule you are merging away.</p>

        <h3 class="doc-subheading">Merge Duplicate Venues</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">There is also a bulk version that groups look-alike venues together and merges a whole group at once. Each group shows how many events sit on each venue, with the most-used one preselected as the one to keep.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Merge</strong> - fold the rest of the group into the selected target.</li>
            <li><strong class="text-gray-900 dark:text-white">Not duplicates</strong> - for venues that only look alike. The group is dismissed for good and stops being suggested.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Work through the groups in any order, and skip any you are unsure about. Nothing is merged until you press the button on that group.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">You can reach it two ways:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">From Following</strong> - covers every venue you are connected to, however you got there. This is the one that finds venues an import or a calendar sync invented and never attached to anything, including ones already deleted.</li>
            <li><strong class="text-gray-900 dark:text-white">From a Curator schedule</strong> - a banner on the <strong class="text-gray-900 dark:text-white">Schedule</strong> tab, scoped to the venues in that schedule's upcoming events.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300">Duplicates are also collapsed in the venue picker when you add an event, so you only see one option per place. That is only done where it is safe: two venues that share a name but each carry their own contact details or address both stay on the list.</p>
    </section>

    <!-- Style -->
    <section id="style" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 3 3 0 005.78-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
            </svg>
            Style
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong class="text-gray-900 dark:text-white">Style</strong> section decides how your public pages look, with a preview beside the form. It has a guide of its own, <a href="{{ route('marketing.docs.schedule_styling') }}" class="doc-link">Schedule Styling</a>. In the section's order:</p>
        <ul class="doc-list">
            <li><strong class="text-gray-900 dark:text-white">On the page as it opens</strong> - <a href="{{ route('marketing.docs.schedule_styling') }}#profile-image" class="doc-link">Square Profile Image</a>, <a href="{{ route('marketing.docs.schedule_styling') }}#color-scheme" class="doc-link">Accent Color</a> and <a href="{{ route('marketing.docs.schedule_styling') }}#typography" class="doc-link">Font Family</a></li>
            <li><strong class="text-gray-900 dark:text-white">Event animation</strong> - <a href="{{ route('marketing.docs.schedule_styling') }}#list-animation" class="doc-link">how event cards arrive</a> as visitors scroll</li>
            <li><strong class="text-gray-900 dark:text-white">Background</strong> - <a href="{{ route('marketing.docs.schedule_styling') }}#backgrounds" class="doc-link">a gradient, a solid color or an image</a></li>
            <li><strong class="text-gray-900 dark:text-white">Header and layout</strong> - <a href="{{ route('marketing.docs.schedule_styling') }}#header-style" class="doc-link">Header Style</a>, <a href="{{ route('marketing.docs.schedule_styling') }}#header-images" class="doc-link">Header Image</a>, <a href="{{ route('marketing.docs.schedule_styling') }}#event-layout" class="doc-link">Default Layout</a> and <a href="{{ route('marketing.docs.schedule_styling') }}#custom-css" class="doc-link">Custom CSS</a> <x-doc-badge plan="pro" /></li>
        </ul>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Gallery section puts up to {{ \App\Utils\GalleryUtils::maxImages() }} photos on your schedule page: the venue, past events, your team. They appear below your events, and a <strong class="text-gray-900 dark:text-white">photos</strong> link in the header opens them all at once. In the list of sections, Gallery shows how many photos it holds.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Adding, arranging and captioning photos works exactly as it does for an event's gallery, and nothing is published until you click <strong class="text-gray-900 dark:text-white">Save</strong>. See <a href="{{ route('marketing.docs.creating_events') }}#gallery" class="doc-link">the event gallery</a> for the details. Each event can have a gallery of its own as well.</p>
        <p class="text-gray-600 dark:text-gray-300">If a schedule drops below Pro, its photos are kept and hidden from guests until the plan is back. They can still be removed in the meantime.</p>
    </section>

    <!-- Videos & Links -->
    <section id="videos-links" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
            Videos & Links
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Two lists, one under the other: <strong class="text-gray-900 dark:text-white">YouTube Videos</strong> and <strong class="text-gray-900 dark:text-white">Social Links</strong>. Both are shown on your public schedule page.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">YouTube Videos</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">+ Add Video</strong>, paste a YouTube link, and it is added with its thumbnail and title. Videos appear in a panel on your schedule page, which the <a href="#settings-advanced" class="doc-link">Hide Videos</a> setting can turn off for Venue and Curator schedules.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Social Links</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">+ Add Link</strong> to add profile URLs (Instagram, Facebook, X, TikTok, Bandcamp, Spotify and so on), or the address of any other site, so visitors can find you elsewhere. A platform Event Schedule recognises is detected from the URL and its icon is used automatically.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Short links</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Every link in the Social Links list gets a short forwarding address under your own schedule URL automatically, with a copy button next to it. A recognised platform is named after the platform, for example <code class="doc-inline-code">yourname.eventschedule.com/instagram</code>, and any other site after its brand name, as long as that name is still free on your schedule: a ticketing partner at <code class="doc-inline-code">promee.co.il/?r=33221</code> answers to <code class="doc-inline-code">yourname.eventschedule.com/promee</code>. Short links are handy in printed material and bios. Each one shows how many times it has been clicked, and the same clicks are counted in <a href="{{ route('marketing.docs.analytics') }}" class="doc-link">Analytics</a>; clicks by you and your team are left out.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Click <strong>Edit</strong> under a link to choose a different address. For a recognised platform the platform address keeps working alongside yours, so a <code class="doc-inline-code">/instagram</code> already printed on a poster never breaks. For any other site your choice replaces the brand-name address. If a link has already been clicked, the editor shows how often and warns you before you change its address. Clear the box to go back to the automatic address. A short link cannot reuse the name of another platform, of a <a href="#customize-subschedules" class="doc-link">sub-schedule</a>, or of a page the app already uses, and where the automatic name is already taken the link simply has none until you pick one.</p>
            </div>
        </div>
    </section>

    <!-- Customize -->
    <section id="customize" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
            </svg>
            Customize
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The <strong class="text-gray-900 dark:text-white">Customize</strong> section is four rows: Sub-schedules, Custom Fields, Categories, and Custom Labels. Closed, each row lists what it holds, so you can read your sub-schedules or see that the categories are still the defaults without opening anything.</p>

        <x-doc-screenshot id="creating-schedules--section-subschedules" alt="The Customize section: rows for Sub-schedules, Custom Fields, Categories and Custom Labels, each listing what it holds" />

        <h3 id="customize-subschedules" class="doc-subheading">Sub-schedules</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Sub-schedules split one schedule into parts, such as "Live Music", "DJ Nights", "Comedy" or "Workshops", or a venue's stages and rooms. Each one gets an address of its own and a color, so visitors can filter your calendar down to the part they care about. Each is one line in the list: its name, its color, and its own address underneath with <strong class="text-gray-900 dark:text-white">Edit</strong> beside it.</p>

        <h3 class="doc-subheading">Creating a sub-schedule</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open the <strong class="text-gray-900 dark:text-white">Customize</strong> section and open the <strong class="text-gray-900 dark:text-white">Sub-schedules</strong> row.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">+ Add Sub-schedule</strong> and type its name. If your schedule is not written in English, a second box beside the name takes it in the language your schedule is translated into, and is labelled with that language, for example <strong class="text-gray-900 dark:text-white">Name (English)</strong>. Leave it blank and the translation fills it in.</li>
            <li>Pick a <strong class="text-gray-900 dark:text-white">Color</strong> from the 14-color palette, or use <strong class="text-gray-900 dark:text-white">Clear</strong> to leave it uncolored. The color is what distinguishes sub-schedules in calendar views and on the filter buttons.</li>
            <li>Save. The sub-schedule now has an address such as <code class="doc-inline-code">yourname.eventschedule.com/live-music</code>, shown with a copy button. <strong class="text-gray-900 dark:text-white">Edit</strong> changes that last part.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The <strong class="text-gray-900 dark:text-white">&times;</strong> at the end of a line takes a sub-schedule off the list. It is only removed when you save, and the bar at the bottom names it after <strong class="text-gray-900 dark:text-white">Saving removes</strong> until you do.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">A sub-schedule sorts, it does not hide</div>
            <p>A sub-schedule carries a name, a color and an address, and nothing else. There is no visibility switch on one, so filing an event under a sub-schedule never hides it. To keep an event off your public page, set the event's own visibility instead - see <a href="{{ route('marketing.docs.creating_events') }}#draft" class="doc-link">event visibility</a>.</p>
        </div>

        <h3 class="doc-subheading">Assigning events to sub-schedules</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">When you create or edit an event, pick a sub-schedule from the dropdown. An event sits in one sub-schedule per schedule, so an event you also share to a Curator schedule can be filed under one of your strands and one of theirs.</p>

        <!-- Custom Fields -->
        <h3 id="customize-custom-fields" class="doc-subheading">Custom Fields <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Define <a href="{{ marketing_url('/features/custom-fields') }}" class="doc-link">Event Custom Fields</a> to add extra data to your events. You can add up to 10 custom fields per schedule, with <strong class="text-gray-900 dark:text-white">+ Add Field</strong>. A saved field shows its number beside it, as <code class="doc-inline-code">&rarr; {custom_1}</code>: that is how you refer to its value in an <a href="#url-pattern-variables" class="doc-link">Event URL Pattern</a>, a <a href="#available-variables" class="doc-link">calendar description</a> and event graphics.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A field shows its name, its type and its checkboxes. What is rarely touched (the name in your schedule's second language, the pattern an answer must match and the note for the AI import) is behind <strong class="text-gray-900 dark:text-white">More options</strong> on each field.</p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Field Name</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Required. The display name for the field.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Type</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Choose from six types: <strong class="text-gray-900 dark:text-white">Text</strong> (one line), <strong class="text-gray-900 dark:text-white">Multi-line Text</strong>, <strong class="text-gray-900 dark:text-white">Yes/No</strong> (a switch), <strong class="text-gray-900 dark:text-white">Date</strong> (a date picker), <strong class="text-gray-900 dark:text-white">Dropdown</strong> (one choice from a list you define), or <strong class="text-gray-900 dark:text-white">Multi-select</strong> (several choices from that list).</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Options</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">For <strong class="text-gray-900 dark:text-white">Dropdown</strong> and <strong class="text-gray-900 dark:text-white">Multi-select</strong> fields, type the choices separated by commas. The field only appears for those two types.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI prompt</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">An optional instruction, up to 500 characters, telling the AI how to extract this field's value when a visitor submits an event through <a href="{{ route('marketing.docs.ai_import') }}" class="doc-link">AI Import</a>. Under More options.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Required</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Mark a field as required so that events cannot be saved without providing a value.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Private</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Hide the field's value from the guest portal. The value still appears in the admin portal and can be referenced in graphic templates and slug patterns via <code class="doc-inline-code">{custom_N}</code>. A private field is never offered as a filter and never shown on the event page, whatever its <strong class="text-gray-900 dark:text-white">Show as filter</strong> and <strong class="text-gray-900 dark:text-white">On event page</strong> settings.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">On event page</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Show the field's answer on the public event page, in a row of its own under the venue: the room, the floor, a dress code. Off until you tick it, for every field, so nothing you already collect is published by accident. One field reads like the date and venue rows above it, and several are listed as pairs. A <strong class="text-gray-900 dark:text-white">Yes/No</strong> field is shown only on events where it is Yes, and an event with no answer shows nothing. Fields are listed in the order you drag them into here. The row appears on every page of the event, including a performer's or a curator's page of it. If the field is also on your request form, remember that a visitor typed the answer: tick this only for answers you are happy to publish once you accept the event. The request form tells the visitor so under the question, and the request card marks such an answer with an eye. If a ticked field does not appear on an event, one of these is why: the event has no answer for it, the field is private, a Yes/No field is No, the event's answers were entered through another schedule's form (an event holds one schedule's answers), your schedule has not accepted the event yet, or your schedule is no longer on Pro.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Show as filter</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Offer the field as a filter on your schedule page, so visitors can narrow the calendar to one value, for example every event in <strong class="text-gray-900 dark:text-white">Room A</strong>. Available for <strong class="text-gray-900 dark:text-white">Text</strong>, <strong class="text-gray-900 dark:text-white">Dropdown</strong> and <strong class="text-gray-900 dark:text-white">Multi-select</strong> fields. It is on by default for dropdown and multi-select fields and off for text fields. Each different value on your events becomes an option in the filter, and matching ignores capital letters and extra spaces, so <code class="doc-inline-code">Room A</code> and <code class="doc-inline-code">room a</code> count as one. A filtered view is a shareable link: choosing a value adds <code class="doc-inline-code">?custom_N=value</code> to the address, where N is the number shown next to the field. See <a href="{{ route('marketing.docs.sharing') }}#embed-parameters" class="doc-link">embed URL parameters</a> to use it in an embed.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">On request form</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ask the field as a question on your public <a href="#engagement-requests" class="doc-link">event request form</a>, so visitors answer it when they submit an event. On by default. Uncheck it to keep a field for your own use in the admin portal. Combine a <strong class="text-gray-900 dark:text-white">Multi-select</strong> field with this to offer a checklist, for example which of your equipment the visitor needs.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Validation Pattern</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Under More options. For <strong class="text-gray-900 dark:text-white">Text</strong> and <strong class="text-gray-900 dark:text-white">Multi-line Text</strong> fields you can require entries to match a pattern, such as a reference code or a phone number. Pick one of the ready-made patterns (email address, phone number, web address, numbers only, letters and numbers) or write your own regular expression, and use the built-in tester to try a sample value before saving. The optional <strong class="text-gray-900 dark:text-white">Hint</strong> is shown under the field so visitors know what format you expect. Patterns are enforced both in the browser and on the server.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Reordering</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Drag a field by its handle to change the order the fields appear in on event forms. The handle shows once there are two fields.</p>
            </div>
        </div>

        <!-- Categories -->
        <h3 id="customize-categories" class="doc-subheading">Categories</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Tailor the event categories shown on your event form and your schedule's filter. The 12 system defaults (Art &amp; Culture, Business Networking, Community, Concerts, Education, Food &amp; Drink, Health &amp; Fitness, Parties &amp; Festivals, Personal Growth, Sports, Spirituality, Tech) are pre-loaded as editable rows. Rename or remove any of them, and add categories that match how you organise events, up to 32 rows in total. A category name can be up to 80 characters. Free on every plan.
        </p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Renaming a default</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Edit the name field on any default row to change how it appears on your schedule. The old name is shown underneath for reference. Existing events tagged with that category automatically display the new name once you save.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Removing a default</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click the <strong class="text-gray-900 dark:text-white">&times;</strong> on any default to remove it from your event form and guest portal filter. Events already tagged with that category keep their badge: the original name still resolves via the system defaults. If the category is in use, you'll see a confirmation showing how many events are affected.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Adding a custom category</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">+ Add category</strong> and enter a name. Custom categories appear immediately in your event form and on the schedule filter.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Color</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Each category can be assigned a color from the 14-color palette. The category's color appears as a small dot beside every event tagged with that category on your schedule, making event types easy to scan at a glance. Categories without a color fall back to the sub-schedule color if one is set.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Ordering</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Categories are always shown alphabetically: there's no manual reordering. The list re-sorts itself when you rename, add, or remove a row.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Translation</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">If your schedule <a href="#details-localization" class="doc-link">offers a second language</a>, custom category names and renamed defaults are translated into it in the background by the same pipeline that translates the rest of your content. Visitors reading in that language see the translated name; everyone else sees the source name you typed.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Reset to default categories</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Removes all customisations and restores the original 12 categories. Existing events keep the category they were assigned. While the list is the original 12, the closed row says <strong class="text-gray-900 dark:text-white">The 12 default categories</strong> in place of naming them all.</p>
            </div>
        </div>
        <div class="doc-callout doc-callout-info mt-4">
            <div class="doc-callout-title">Cross-schedule viewing</div>
            <p>When a curator schedule shares an event from a talent schedule, the event's category badge shows the original name chosen by the talent. The curator's filter dropdown dynamically includes any categories present in visible events, so foreign categories remain filterable.</p>
        </div>

        <!-- Custom Labels -->
        <h3 id="customize-custom-labels" class="doc-subheading">Custom Labels <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Override the wording Event Schedule uses on your public schedule page. For example, change "Events" to "Shows", "Follow" to "Subscribe", or "Free entry" to "No cover charge".
        </p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Adding a custom label</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pick a label from the searchable dropdown and click <strong class="text-gray-900 dark:text-white">Add</strong>, then type your replacement, up to 200 characters. A label already overridden drops out of the dropdown, and the <strong class="text-gray-900 dark:text-white">&times;</strong> at the end of its line puts it back and restores the original wording once you save.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Available labels</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ count(\App\Models\Role::getCustomizableLabels()) }} labels in all, covering the buttons (Request to Book, Submit Event, Follow, Buy Tickets, Book a Time, Register, Share), the navigation (Events, Filter Events, Past Events, Load More, Show All, View Full Schedule), and the wording on events themselves (Free entry, Online, Schedule, Category, Venue, Agenda, About, Photo Gallery, Our Sponsors).</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Translations</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">If your schedule is not written in English, a second box appears under each label for the translated wording. Leave it blank and the translation fills it in for you.</p>
            </div>
        </div>
    </section>

    <!-- Settings -->
    <section id="settings" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Settings
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The <strong class="text-gray-900 dark:text-white">Settings</strong> section controls how your schedule behaves. The schedule's address is on the page as it opens. <strong class="text-gray-900 dark:text-white">Notifications</strong> and <strong class="text-gray-900 dark:text-white">Advanced</strong> are two rows under it: closed, Notifications lists the emails that are switched on and Advanced lists its three headings.</p>

        <x-doc-screenshot id="creating-schedules--section-settings" alt="The Settings section: the Schedule URL with its copy button and Edit, then the Notifications row and the Advanced row, each saying what it holds" />

        <!-- General Tab -->
        <h3 id="settings-general" class="doc-subheading">Schedule URL</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule URL</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your schedule's address, shown with a copy button. Click <strong class="text-gray-900 dark:text-white">Edit</strong> beside it to change it. On the hosted platform it is a subdomain, <code class="doc-inline-code">yourname.eventschedule.com</code>; on a selfhosted install it is a path, <code class="doc-inline-code">yoursite.com/yourname</code>. Between 4 and 50 characters, lowercase letters, numbers and dashes only. Choose something memorable and easy to type, because changing it later breaks any link people have already saved. The same address is under the schedule's name at the top of the form, with <strong class="text-gray-900 dark:text-white">Copy</strong> and <strong class="text-gray-900 dark:text-white">View</strong>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Custom Domain <x-doc-badge plan="enterprise" /></h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Use your own domain, for example <code class="doc-inline-code">events.yourbrand.com</code>, instead of a subdomain.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">On the hosted platform, clicking <strong class="text-gray-900 dark:text-white">Edit</strong> beside the Schedule URL also shows a <strong class="text-gray-900 dark:text-white">Mode</strong> with three choices: <strong class="text-gray-900 dark:text-white">Subdomain</strong> (the default, no custom domain), <strong class="text-gray-900 dark:text-white">Redirect</strong>, and <strong class="text-gray-900 dark:text-white">Direct</strong>. The last two need Enterprise; below it they are greyed out. A selfhosted install has no Mode, only the path. See the <a href="#custom-domain" class="doc-link">setup instructions</a> below.</p>
            </div>
        </div>

        <!-- Custom Domain -->
        <h3 id="custom-domain" class="doc-subheading">Custom Domain Setup <x-doc-badge plan="enterprise" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            There are two ways to connect a custom domain to your schedule. Choose the mode that best fits your needs.
        </p>

        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Direct Mode (CNAME)</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Your schedule is served directly on your custom domain with automatic SSL. This is the recommended option for most users.
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>In the <strong class="text-gray-900 dark:text-white">Settings</strong> section, click <strong class="text-gray-900 dark:text-white">Edit</strong> beside the Schedule URL, select <strong class="text-gray-900 dark:text-white">Direct</strong>, and enter your domain (e.g., <code class="doc-inline-code">events.yourbrand.com</code>) under <strong class="text-gray-900 dark:text-white">Custom Domain</strong>. The form shows the CNAME target with a copy button.</li>
            <li>Go to your domain registrar (e.g., GoDaddy, Namecheap, Cloudflare) and create a <strong class="text-gray-900 dark:text-white">CNAME record</strong> pointing your domain to <code class="doc-inline-code">{{ config('services.digitalocean.app_hostname') }}</code>.</li>
            <li>Wait for DNS propagation (usually a few minutes, but can take up to 48 hours).</li>
            <li>SSL is provisioned automatically. Once DNS has propagated, your schedule will be accessible at your custom domain over HTTPS.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6">While a Direct domain is being set up, a status sits beside the Schedule URL: <strong class="text-gray-900 dark:text-white">Setting up...</strong>, then nothing once it is live, or <strong class="text-gray-900 dark:text-white">Setup failed</strong>. Until the domain is live, the address shown at the top of the form and of the schedule's pages stays the eventschedule.com one, because that is the one that answers.</p>

        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Redirect Mode (Cloudflare)</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Your custom domain redirects visitors to your <code class="doc-inline-code">eventschedule.com</code> URL. Use this if your domain's DNS is managed by Cloudflare. Cloudflare's free plan is sufficient.
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>In the <strong class="text-gray-900 dark:text-white">Settings</strong> section, click <strong class="text-gray-900 dark:text-white">Edit</strong> beside the Schedule URL, select <strong class="text-gray-900 dark:text-white">Redirect</strong>, and enter your domain under <strong class="text-gray-900 dark:text-white">Custom Domain</strong>.</li>
            <li>
                <strong class="text-gray-900 dark:text-white">Add your domain to Cloudflare</strong> (if not already). After adding the domain, Cloudflare will provide two nameservers. Go to your domain registrar and update your domain's nameservers to the ones Cloudflare provides. Wait for Cloudflare to confirm the domain is active.
            </li>
            <li>
                <strong class="text-gray-900 dark:text-white">Set up DNS records.</strong> In your Cloudflare dashboard, go to <strong class="text-gray-900 dark:text-white">DNS > Records</strong>:
                <ul class="list-disc ml-6 mt-2 mb-2">
                    <li>Delete any existing A or AAAA records for the domain.</li>
                    <li>Add an <strong class="text-gray-900 dark:text-white">A record</strong> with the name <code class="doc-inline-code">@</code> (root domain) pointing to <code class="doc-inline-code">192.0.2.1</code>.</li>
                    <li>Add another <strong class="text-gray-900 dark:text-white">A record</strong> with the name <code class="doc-inline-code">*</code> (wildcard) pointing to <code class="doc-inline-code">192.0.2.1</code>.</li>
                    <li>The IP address doesn't matter since traffic will be redirected. Make sure both records are set to <strong class="text-gray-900 dark:text-white">Proxied</strong> (orange cloud icon) so Cloudflare can intercept and redirect the requests.</li>
                </ul>
            </li>
            <li>
                <strong class="text-gray-900 dark:text-white">Create a Page Rule.</strong> In your Cloudflare dashboard, go to <strong class="text-gray-900 dark:text-white">Rules > Page Rules</strong> and create a new page rule:
                <ul class="list-disc ml-6 mt-2 mb-2">
                    <li><strong class="text-gray-900 dark:text-white">URL pattern:</strong> <code class="doc-inline-code">*yourdomain.com/*</code></li>
                    <li><strong class="text-gray-900 dark:text-white">Setting:</strong> Forwarding URL</li>
                    <li><strong class="text-gray-900 dark:text-white">Status code:</strong> 301 - Permanent Redirect</li>
                    <li><strong class="text-gray-900 dark:text-white">Destination URL:</strong> <code class="doc-inline-code">https://yourname.eventschedule.com/$2</code></li>
                </ul>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">The <code class="doc-inline-code">$2</code> wildcard preserves the URL path, so <code class="doc-inline-code">yourdomain.com/some-event</code> correctly redirects to <code class="doc-inline-code">yourname.eventschedule.com/some-event</code>.</p>
            </li>
            <li>Changes may take a few minutes to several hours to propagate. Once active, visitors who go to your custom domain will be seamlessly redirected to your schedule.</li>
        </ol>

        <!-- Notifications Tab -->
        <h3 id="settings-notifications" class="doc-subheading">Notifications</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The row is in three groups, by who the email goes to: <strong class="text-gray-900 dark:text-white">Emails to you</strong>, <strong class="text-gray-900 dark:text-white">Emails to followers</strong> and <strong class="text-gray-900 dark:text-white">Shared inbox</strong>. Where the site has push notifications set up, a <strong class="text-gray-900 dark:text-white">Push notifications</strong> panel sits above the three.</p>

        <h4 id="notifications-push" class="font-semibold text-gray-900 dark:text-white mt-6 mb-2">Push notifications <x-doc-badge plan="pro" /></h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The same notifications can also reach you as browser and mobile push, on top of email rather than instead of it. Choose <strong class="text-gray-900 dark:text-white">Enable push on this device</strong>, allow notifications when your browser asks, then use <strong class="text-gray-900 dark:text-white">Send test push</strong> to confirm it works. Push is per device, so repeat it on your phone and your laptop. Enabling it sends notification data to OneSignal, a third-party service.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The panel only appears once the operator of your Event Schedule site has configured push, which is <a href="{{ route('marketing.docs.selfhost.installation') }}#push-notifications" class="doc-link">off by default</a>. On iPhone and iPad, web push only works for sites added to the home screen (iOS 16.4 and later); Android and desktop browsers need no such step.</p>

        <h4 id="notifications-to-you" class="font-semibold text-gray-900 dark:text-white mt-6 mb-2">Emails to you</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Choose which emails you get about this schedule. These choices are personal: each owner and admin sets their own in this row, and the emails go to the address on that person's account, not to the schedule's <a href="#contact-info" class="doc-link">contact email</a>. To also send them to a team inbox, add a <a href="#notification-email" class="doc-link">shared notification address</a>. <strong class="text-gray-900 dark:text-white">New event requests</strong>, <strong class="text-gray-900 dark:text-white">Installment payments</strong> and <strong class="text-gray-900 dark:text-white">Weekly summary</strong> start switched on and stay that way until you turn them off; the rest are off until you turn them on.</p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">New event requests</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive an email when new event requests are pending approval. The email names each new request: what is asked for, who asked, when, how to reach them and their answers to your request form's questions.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">New fan content</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive an email when fans submit new videos, comments or photos for your events.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">New ticket sale</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive an email when a ticket is purchased for one of your events. On the free plan this covers the first paid sale on each event; Pro emails you about every sale.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">New feedback</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive an email when attendees submit post-event feedback for your events.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">New poll option suggestions</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive an email when a visitor suggests a new option on one of your event polls (requires <a href="{{ route('marketing.docs.creating_events') }}#polls" class="doc-link">Allow User Options</a> to be enabled).</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Installment payments</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A daily summary of <a href="{{ route('marketing.docs.tickets') }}#installments" class="doc-link">installment payments</a> due in the next two days, and an immediate email whenever one fails. It only matters if you let buyers pay in installments, which is a Pro feature.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Feeds</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">An email when drafts from a <a href="{{ route('marketing.docs.managing_schedules') }}#feeds" class="doc-link">feed</a> are waiting for review, when an event people signed up for changed at its source, and when a feed cannot be read. At most one of each a day. On by default, and shown where the schedule can have feeds.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Weekly summary</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">One email a week with your views, sign-ups, sales and the dates coming up, for all of your schedules together. It is only sent in a week with something to report. The switch is shown to the schedule's owner alone, who is the only one to receive it, and the email is sent on the hosted platform only.</p>
            </div>
        </div>
        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Three of them need your own email settings</div>
            <p>On the hosted platform, <strong class="text-gray-900 dark:text-white">New ticket sale</strong>, <strong class="text-gray-900 dark:text-white">New feedback</strong> and <strong class="text-gray-900 dark:text-white">New poll option suggestions</strong> only email you once you configure <a href="#integrations-email" class="doc-link">Email Settings</a>, and a note in the row links you straight there. Until then their toggles are greyed out, except that New ticket sale and New feedback stay usable where push notifications are available (above): on Pro those two also arrive as a push, which needs no email settings. A greyed-out toggle keeps whatever it was set to. New event requests, new fan content, <strong class="text-gray-900 dark:text-white">Installment payments</strong> and the weekly summary work either way. Selfhosted installs send everything through the server's own mail configuration, so nothing is gated.</p>
        </div>

        <h4 id="notifications-to-followers" class="font-semibold text-gray-900 dark:text-white mt-6 mb-2">Emails to followers</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The one setting in this group points the other way, and it belongs to the whole schedule rather than to you. <strong class="text-gray-900 dark:text-white">Email subscribers about new events</strong> decides whether your <a href="{{ route('marketing.docs.newsletters') }}#email-subscribers" class="doc-link">email subscribers</a> get an automatic digest when you publish, at most one every few days. It covers the events this schedule created itself, so an event another schedule lists on yours, including everything a curator pulls in through its <a href="#event-sources" class="doc-link">event sources</a>, never appears in it. It is on by default, because the people receiving it asked for it when they signed up.</p>

        <h4 id="notification-email" class="font-semibold text-gray-900 dark:text-white mt-6 mb-2">Shared notification address</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If your team works from a shared mailbox, enter it under <strong class="text-gray-900 dark:text-white">Shared notification address</strong>, in the <strong class="text-gray-900 dark:text-white">Shared inbox</strong> group at the bottom of the Notifications row. It gets a copy of the schedule's notifications on top of each person's own emails, so it replaces nothing. Unlike the toggles above, it belongs to the whole schedule: there is one per schedule, and any owner or admin can change it.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Nothing is sent to it until it is confirmed. Saving the address emails it a confirmation link, valid for {{ \App\Services\NotificationEmailService::VERIFY_TTL_DAYS }} days, and the row shows <strong class="text-gray-900 dark:text-white">Waiting for confirmation</strong> with a button to send the link again. Anyone who reads that mailbox can confirm it, with or without an account. Changing the address asks for confirmation again.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Under <strong class="text-gray-900 dark:text-white">Send to this address</strong> the address has its own set of toggles: <strong class="text-gray-900 dark:text-white">New event requests</strong> (on by default), <strong class="text-gray-900 dark:text-white">New ticket sale</strong>, <strong class="text-gray-900 dark:text-white">New feedback</strong>, <strong class="text-gray-900 dark:text-white">New poll option suggestions</strong>, <strong class="text-gray-900 dark:text-white">Installment payments</strong> and <strong class="text-gray-900 dark:text-white">Feeds</strong> (on by default). New fan content is not offered, because that email only ever goes to the person who created the event. Each one follows the same rules as your own copy, including the plan and email settings rules above. If the shared address is also the login of someone who already gets that email, it is not sent twice. The request email carries what the requester sent, their contact details and their answers to private fields included, so choose a mailbox the whole team is meant to read.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Every email sent to the shared address ends with a <strong class="text-gray-900 dark:text-white">Stop sending to this address</strong> link, which removes it without signing in. It is also removed when the schedule is <a href="{{ route('marketing.docs.managing_schedules') }}#transfer-ownership" class="doc-link">transferred</a> to a new owner and the previous owner leaves.</p>

        <!-- Advanced Tab -->
        <h3 id="settings-advanced" class="doc-subheading">Advanced</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The Advanced row collects the settings that change how your schedule behaves rather than how it looks, under three headings: <strong class="text-gray-900 dark:text-white">New events</strong>, <strong class="text-gray-900 dark:text-white">Public page</strong> and, on the hosted platform, <strong class="text-gray-900 dark:text-white">AI Import</strong>. Closed, the row lists the headings.</p>
        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 mt-6">New events</h4>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Default visibility for new events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The visibility every new event starts with: <strong class="text-gray-900 dark:text-white">Public</strong> or <strong class="text-gray-900 dark:text-white">Draft</strong>, plus <strong class="text-gray-900 dark:text-white">Internal</strong> and <strong class="text-gray-900 dark:text-white">Unlisted</strong> on Enterprise. Public unless you change it, and you can still set the visibility on any individual event. See <a href="{{ route('marketing.docs.creating_events') }}#draft" class="doc-link">event visibility</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Event URL Pattern</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Decides the address given to each new event. Leave it empty and the event name is used. Otherwise build a pattern from the <a href="#url-pattern-variables" class="doc-link">variables below</a>, for example <code class="doc-inline-code">{event_name}-{date_dmy}</code>, which produces addresses like <code class="doc-inline-code">my-event-27-1</code>.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Changing the pattern only affects events created from then on, so an <strong class="text-gray-900 dark:text-white">Update all events</strong> button appears offering to apply it to your existing events as well. <strong class="text-gray-900 dark:text-white">Show available variables</strong>, under the field, opens the list below.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Default category</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Preselect one of your <a href="#customize-categories" class="doc-link">categories</a> on every new event so you do not have to pick one each time. Once saved, an <strong class="text-gray-900 dark:text-white">Update all events</strong> button appears to apply the default to all existing events in one click. If you later remove the category, the setting is flagged so you can pick another.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Default curator schedules</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">If you also run Curator schedules, tick the ones new events should be shared to automatically, instead of choosing them on every event. Shown on Talent and Venue schedules that have at least one Curator schedule to offer.</p>
            </div>
        </div>
        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 mt-6">Public page</h4>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Hide Past Events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Keep past events off your public schedule so visitors only ever see what is still to come. Your own admin views are unaffected, so nothing is lost: the events are still there when you need them.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Hide Videos</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Hide the videos panel from your public schedule. Offered on <strong class="text-gray-900 dark:text-white">Venue</strong> and <strong class="text-gray-900 dark:text-white">Curator</strong> schedules only, because a Talent schedule's videos are part of the point.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">First Day of Week</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Which day your calendar week starts on. All seven days are available; Sunday unless you change it.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Show Accessibility Widget</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Add an accessibility panel to your public schedule so visitors can adjust font size, contrast, and motion for themselves. Useful if you are publishing on behalf of an organization with an accessibility commitment to meet.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Show Sign-Up Panel</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">On unless you turn it off. Signed-out visitors see the <strong class="text-gray-900 dark:text-white">Stay up to date</strong> panel on your schedule page and near the foot of each event page, where they can sign up for <a href="{{ route('marketing.docs.sharing') }}#followers" class="doc-link">email updates</a>. Turning it off also removes the calendar feed link under the form. A link made to open the form, like the QR code on the Followers tab, still shows the panel. The switch only hides it: sign-ups through such a link, and on eventschedule.com through the <strong class="text-gray-900 dark:text-white">Follow</strong> button, still arrive. Free on every plan.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Show &ldquo;Notify Me&rdquo; Card</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Off unless you turn it on. When it is on, the public events this schedule creates offer a <strong class="text-gray-900 dark:text-white">Tell me when tickets go on sale</strong> card, plus links to it in the Add to Calendar menu and beside the buy button, so visitors can join that event's <a href="{{ route('marketing.docs.tickets') }}#interest-list" class="doc-link">interest list</a>. It follows the event wherever it is listed, including a performer's or curator's page, because the list and its emails belong to the schedule that created the event. Turning it off again stops new sign-ups, and anyone already on a list still gets the emails they asked for. Free on every plan.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Do not show other schedules' promotions</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Some Event Schedule sites run a promotions network, where schedules pay to have an event featured on other schedules' public pages. Turn this on and your pages carry nothing of the sort: no other schedule's promotions, and no ads either. It is free on every plan, and it does not stop you buying promotions of your own. See <a href="{{ route('marketing.docs.boost') }}#on-network" class="doc-link">on-network promotions</a> and <a href="{{ route('marketing.docs.managing_schedules') }}#plan" class="doc-link">ads on free schedules</a>. The toggle only appears on sites that have this switched on.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">List this schedule on the network</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Share this schedule's public events with the listings on eventschedule.com, where each listing links back to the event on your own site. Three choices: <strong class="text-gray-900 dark:text-white">Not decided yet</strong>, <strong class="text-gray-900 dark:text-white">Listed on the network</strong> or <strong class="text-gray-900 dark:text-white">Not listed</strong>. The setting only appears once an administrator has enabled federation for the whole installation, so you will not see it on eventschedule.com itself. You do not have to come here to answer it: once the schedule has an upcoming public event with an image, a <strong class="text-gray-900 dark:text-white">List on the network</strong> prompt on the schedule's page, and on your dashboard for schedules you own, does it in one click. A listed schedule shows <strong class="text-gray-900 dark:text-white">Listed on the network</strong> on its page, which links back to this setting. See <a href="{{ route('marketing.docs.selfhost.federation') }}#per-schedule" class="doc-link">Federation</a>.</p>
            </div>
        </div>
        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 mt-6">AI Import</h4>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Import Form Fields</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Decides which optional fields appear on the AI import <a href="#engagement-requests" class="doc-link">request form</a>: short description, description, price, coupon code, registration URL, category, and sub-schedule if you have any. The coupon code field brings its discount along with it. Turn a field on and a <strong class="text-gray-900 dark:text-white">Required</strong> checkbox appears next to it, so you can insist on an answer. Shown on the hosted platform only.</p>
            </div>
        </div>

        <!-- URL Pattern Variables -->
        <h3 id="url-pattern-variables" class="doc-subheading">URL Pattern Variables</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Use these in the <strong class="text-gray-900 dark:text-white">Event URL Pattern</strong>, in the Advanced row above. Every value is converted to something safe for a URL: lowercase, with spaces and punctuation turned into dashes. These are close cousins of the <a href="#available-variables" class="doc-link">calendar description variables</a> but not identical, because a URL cannot carry a slash or a colon: dates here use dashes and never gain a year, and there is no variable for the description or the event's own link.
        </p>

        <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Date & Time</h5>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">{day_name}</code></td>
                        <td>Full day name (translated)</td>
                        <td>wednesday</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{day_short}</code></td>
                        <td>Short day name (translated)</td>
                        <td>wed</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{date_dmy}</code></td>
                        <td>Day-month format</td>
                        <td>15-3</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{date_mdy}</code></td>
                        <td>Month-day format</td>
                        <td>3-15</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{date_full_dmy}</code></td>
                        <td>Full date (day-month-year)</td>
                        <td>15-03-2025</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{date_full_mdy}</code></td>
                        <td>Full date (month-day-year)</td>
                        <td>03-15-2025</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{month}</code></td>
                        <td>Month number</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{month_pad}</code></td>
                        <td>Month number (zero-padded)</td>
                        <td>03</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{month_name}</code></td>
                        <td>Full month name (translated)</td>
                        <td>march</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{month_short}</code></td>
                        <td>Short month name (translated)</td>
                        <td>mar</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{day}</code></td>
                        <td>Day of month</td>
                        <td>15</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{day_pad}</code></td>
                        <td>Day of month (zero-padded)</td>
                        <td>05</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{year}</code></td>
                        <td>Year</td>
                        <td>2025</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{time}</code></td>
                        <td>Start time</td>
                        <td>20-00 or 8-00-pm</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{end_time}</code></td>
                        <td>End time</td>
                        <td>22-00 or 10-00-pm</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{duration}</code></td>
                        <td>Duration in hours</td>
                        <td>2</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Event Information</h5>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">{event_name}</code></td>
                        <td>Event Name</td>
                        <td>summer-concert</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Venue Information</h5>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">{venue}</code></td>
                        <td>Venue name (translated)</td>
                        <td>central-park</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{city}</code></td>
                        <td>City</td>
                        <td>new-york</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{address}</code></td>
                        <td>Street address</td>
                        <td>123-main-st</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{state}</code></td>
                        <td>State/Province</td>
                        <td>ny</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{country}</code></td>
                        <td>Country</td>
                        <td>us</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3"><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Ticket</a> Information</h5>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">{currency}</code></td>
                        <td>Currency code</td>
                        <td>usd</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{price}</code></td>
                        <td>Lowest ticket price (or price range)</td>
                        <td>10 or 10-25</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{coupon_code}</code></td>
                        <td>Coupon code</td>
                        <td>SAVE20</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Custom Fields <x-doc-badge plan="pro" /></h5>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The value an event holds for one of your <a href="#customize-custom-fields" class="doc-link">custom fields</a>, by the field's number.</p>
        @if (!empty($customFieldsData))
            {{-- Dynamic: Show user's actual custom fields --}}
            @foreach ($customFieldsData as $scheduleData)
                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">{{ $scheduleData['role_name'] }}</h4>
                <div class="doc-table-wrap">
                    <table class="doc-table">
                        <thead>
                            <tr>
                                <th>Variable</th>
                                <th>Field Name</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($scheduleData['fields'] as $index => $field)
                            <tr>
                                <td><code class="doc-inline-code">{custom_{{ $field['index'] ?? $loop->iteration }}}</code></td>
                                <td>{{ $field['name'] }}</td>
                                <td>{{ __('messages.type_'.($field['type'] ?? 'string')) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @else
            {{-- Static: Generic documentation for logged-out users or users without custom fields --}}
            <div class="doc-table-wrap">
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Description</th>
                            <th>Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code class="doc-inline-code">{custom_1}</code></td>
                            <td>Value of the 1st custom field</td>
                            <td>john-smith</td>
                        </tr>
                        <tr>
                            <td><code class="doc-inline-code">{custom_2}</code></td>
                            <td>Value of the 2nd custom field</td>
                            <td>room-101</td>
                        </tr>
                        <tr>
                            <td><code class="doc-inline-code">{custom_3}</code></td>
                            <td>Value of the 3rd custom field</td>
                            <td>workshop</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-gray-400 text-sm">...up to {custom_10}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">URL-Safe Formatting</div>
            <p>All variable values are automatically converted to URL-safe slugs: lowercase letters, numbers, and dashes only. For example, "Summer Concert" becomes "summer-concert" and "New York" becomes "new-york".</p>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">Everything to do with what visitors can send you, in six rows that each say whether they are on: Requests, Fan Content, Feedback, Carpool, Sponsors, and Accommodation. The last one only appears on sites whose operator has enabled it. Nothing here is on the page until you open a row.</p>

        <x-doc-screenshot id="creating-schedules--section-engagement" alt="The Engagement section: rows for Requests, Fan Content, Feedback, Carpool and Sponsors, each saying whether it is on" />

        <!-- Requests Tab -->
        <h3 id="engagement-requests" class="doc-subheading">Requests</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Let other people put events on your schedule. The row opens on one switch, <strong class="text-gray-900 dark:text-white">Accept requests</strong>, and the rest appears once that is on.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A <strong class="text-gray-900 dark:text-white">Talent</strong> schedule gets a shorter version of this row, with only <strong class="text-gray-900 dark:text-white">Accept requests</strong>, the Booking Form options, <strong class="text-gray-900 dark:text-white">Request Terms</strong> and your own questions, because a request to book a performer is always read by hand. The same goes for events other schedules add you to: once your talent schedule has an owner, a venue or curator that lists you sends a request you accept under <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Requests</strong>, unless it had already listed you before you claimed your page. Requests to a talent always come in through the Booking Form, where creating an account is left to the visitor. Whoever sends a request through the Booking Form gives their name and email, and those are shown with the request under <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Requests</strong> so you can reply to them.</p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Accept requests</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Turn on the public request form. What lands there is reviewed in the <a href="{{ route('marketing.docs.creating_events') }}#manual" class="doc-link">pending queue</a>. Typical uses:</p>
                <ul class="text-sm text-gray-500 dark:text-gray-400 list-disc list-inside space-y-1">
                    <li><strong class="text-gray-900 dark:text-white">Talent:</strong> let promoters ask to book you</li>
                    <li><strong class="text-gray-900 dark:text-white">Venue:</strong> take booking requests from bands and performers</li>
                    <li><strong class="text-gray-900 dark:text-white">Curator:</strong> let the community submit local events</li>
                </ul>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Require Account</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Make submitters sign in first, so every request has a name behind it. On by default for Curator schedules, off for Venue schedules. With the AI Import form, a first-time submitter completes everything on one page - their account, their own schedule, and the event - with their email confirmed by a code after they press Submit. They can add a flyer and have the form filled in from it. With it off, visitors can send a request as a guest, and on the Booking Form they can also choose to create an account, wherever the site accepts new accounts. Not offered on Talent schedules, whose Booking Form always leaves the account up to the visitor.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Event submission form</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Which form visitors see: <strong class="text-gray-900 dark:text-white">AI Import</strong>, where they paste the event text or upload a flyer and the details are read out of it, or <strong class="text-gray-900 dark:text-white">Booking Form</strong>, a plain form with set fields. AI Import unless you change it. Offered on Venue and Curator schedules while <strong class="text-gray-900 dark:text-white">Require Account</strong> is off; a Talent schedule always uses the Booking Form.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Required fields</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Choose which of the Booking Form's own fields a visitor has to fill in before sending a request: <strong class="text-gray-900 dark:text-white">Event Name</strong>, <strong class="text-gray-900 dark:text-white">Date &amp; Time</strong>, <strong class="text-gray-900 dark:text-white">Description</strong> and <strong class="text-gray-900 dark:text-white">Location</strong>. Nothing is required until you tick it. Location is satisfied by a venue name, address or city, or by ticking Online, and is not offered on Venue schedules, where your venue is the location. To insist on an answer to one of your own questions, use that question's <strong class="text-gray-900 dark:text-white">Required</strong> option, and for a phone number use <strong class="text-gray-900 dark:text-white">Ask for phone number</strong> below. Shown while the Booking Form is in use.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Offer online events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">On by default. Turn it off and the Booking Form no longer offers the <strong class="text-gray-900 dark:text-white">Online</strong> option, so every request is for an in-person event. The AI Import form is not affected.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Ask for phone number</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Off by default. Turn it on and the Booking Form asks the visitor for a phone number, and a <strong class="text-gray-900 dark:text-white">Required</strong> switch appears beneath it if you want to insist on one. Signed-in visitors are asked too, since the form does not take one from their account. The number is shown with the request under <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Requests</strong>, as a link you can tap to call. The AI Import form is not affected.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Require Approval</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">On by default. Submitted events wait in the <a href="{{ route('marketing.docs.creating_events') }}#manual" class="doc-link">pending queue</a> until you accept them; turn it off and they go straight onto your public schedule. With it off, the Booking Form asks every visitor for an event name, a date and a start time, whatever you left optional: the event is public the moment it is sent. A request from someone on the schedule's own team skips the queue either way. Review them under <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Requests</strong>. Not offered on Talent schedules.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Approved Schedules</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Name schedules you already trust and what they send from their own schedule skips the queue: an event of theirs that they add you to, or a submission they make signed in while <strong class="text-gray-900 dark:text-white">Require Account</strong> is on. A Booking Form request, or a submission sent without an account, waits for approval like everyone else's. Click <strong class="text-gray-900 dark:text-white">+ Add Schedule</strong>, start typing to search, and pick a schedule. Shown while <strong class="text-gray-900 dark:text-white">Require Approval</strong> is on, and not offered on Talent schedules.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Request Terms</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Terms or guidelines for people sending you requests. Use it for booking policy, technical requirements, or what you will and will not take. They are shown on every request form, where they are read before anything is typed: under the title of the Booking Form and of the page where a signed-up submitter enters their event, and on the AI Import form.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Your own questions <x-doc-badge plan="pro" /></h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Add questions of your own to whichever form you use, with <a href="#customize-custom-fields" class="doc-link">Custom Fields</a> marked <strong class="text-gray-900 dark:text-white">On request form</strong>. Ask whatever you need before accepting an event: which of your equipment the visitor wants (a multi-select checklist), a reference number in a set format (a validation pattern), an expected head count. Answers appear on the request in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Requests</strong>, in the email that announces it, and on the event once you accept it. A link at the bottom of this row opens the Custom Fields row.</p>
            </div>
        </div>

        <!-- Fan Content Tab -->
        <h3 id="engagement-fan-content" class="doc-subheading">Fan Content</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Let people who were there add to the event afterwards. Each kind has its own switch, all three are off until you turn them on, and nothing a visitor sends appears in public until you approve it.</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Comments</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Written comments on your events.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Photos</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Uploaded photos. A Free schedule holds up to 25 photos in total; Pro removes the cap and adds a bulk download of every photo on an event.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Videos</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Links to YouTube videos.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Require an account</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Off by default: attendees submit with just a name and email, so they never have to create an account. Turn it on to make them sign in first. Either way nothing appears publicly until you approve it, and the submitter's email is only ever visible to you.</p>
            </div>
        </div>

        <!-- Feedback Tab -->
        <h3 id="engagement-feedback" class="doc-subheading">Feedback <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Ask attendees what they thought once the event is over. Free schedules see the row with its switch greyed out. On the hosted platform, feedback emails also need <a href="#integrations-email" class="doc-link">Email Settings</a>: until they are configured the switch stays greyed out, and a note in the row links you there.</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Post-event feedback</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Once the event has ended, email everyone holding a ticket or registration for it, asking for a star rating and a comment. The rest of the row appears once this is on.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Send feedback request after</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">How long to wait after the event ends: 1, 2, 6, 12, 24 or 48 hours. 24 hours unless you change it.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Show feedback publicly</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Publish the ratings and comments you collect on the event page, so people deciding whether to come can see what previous attendees said. Off by default: with it off, feedback is yours alone to read.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Send test feedback email</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A button that sends you the request email as an attendee would receive it, so you can check the wording and the delivery before an event ends.</p>
            </div>
        </div>

        <h3 id="engagement-carpool" class="doc-subheading">Carpool <x-doc-badge plan="pro" /></h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Carpool matching</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Let attendees arrange lifts to and from your events. A carpool link appears on the event page where they can offer a ride or ask for a seat.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">How it works</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A driver posts an offer with their city, the direction (to the event, from it, or both), how many seats are free, and optionally a departure time and meeting point. Attendees browse the offers and ask for a seat, the driver accepts or declines, and accepted passengers are given the driver's contact details.</p>
            </div>
        </div>

        <!-- Sponsors -->
        <h3 id="engagement-sponsors" class="doc-subheading">Sponsors <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Show the people backing you in a band across your public schedule page.
        </p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Adding sponsors</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">+ Add Sponsor</strong> to open the form. A <strong class="text-gray-900 dark:text-white">Logo</strong> is required; the <strong class="text-gray-900 dark:text-white">Sponsor Name</strong>, a <strong class="text-gray-900 dark:text-white">Website URL</strong> and a <strong class="text-gray-900 dark:text-white">Tier</strong> of Gold, Silver or Bronze are optional. Up to {{ config('app.max_sponsors') }} sponsors per schedule. Nothing is published until you save the form.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Reordering</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Drag the handle on a sponsor to change the order they appear in on the public page.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Background</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Shown once you have a sponsor. Choose how the sponsors band blends into your page: <strong class="text-gray-900 dark:text-white">Default</strong> (the panel), <strong class="text-gray-900 dark:text-white">Transparent</strong> so your own background shows through, or <strong class="text-gray-900 dark:text-white">Custom color</strong>. Text colors adjust automatically for readability.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Show sponsors</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Shown once you have a sponsor. Switch it off to take the sponsors section off your schedule page and your event pages without deleting anything; switch it back on and the same sponsors return. An event with sponsors of its own keeps showing them, and a sponsors block you place in a newsletter is not affected. The switch stays available on every plan, so sponsors added on a paid plan can always be hidden.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-tip mt-4">
            <div class="doc-callout-title">Tip</div>
            <p>You can also override sponsors for individual events. See <a href="{{ route('marketing.docs.creating_events') }}#sponsors" class="doc-link">Per-Event Sponsors</a>.</p>
        </div>

        <!-- Venue map -->
        <h3 id="engagement-venue-map" class="doc-subheading">Venue map</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Show the venues of your events as pins on a map on your public schedule page, each with the venue's logo on its pin. A visitor presses a venue to see what is coming up there, get directions and find what else is nearby.
        </p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Show a map of venues</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Off by default. Switch it on and save. Each venue's address is then looked up once: a dozen or so straight away and the rest four a minute, and your schedule's admin page shows how far that is. The map is put on your page when every venue has been looked up, and it is there only while at least two venues have a position. A venue you add later joins the map when it has been placed.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Open the map on arrival</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The map is a band above your events that a visitor opens. With this on, it starts open on larger screens for visitors who have allowed <strong class="text-gray-900 dark:text-white">Marketing and embedded content</strong> in the cookie notice, and the street images then load with the page. Where your Event Schedule instance uses no street images, it starts open for everyone. On a phone it always starts closed, and a visitor who hides it keeps it hidden.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Which venues are on it</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The venues of your public events from the last 60 days onward. A venue with nothing coming up is a small grey dot. Events that are a draft, cancelled, unlisted or behind a password do not count. Neither does a venue that has an owner and has not accepted the event, whether it declined or has not answered yet: it is not in your list of venues either. A recurring event keeps its venue on the map for as long as the series runs. An online event whose location is a meeting link is not a place and is left out. On a sub-schedule's page the map holds that sub-schedule's venues.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Venues</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Once the map is on, this list names every venue and where it stands: <strong class="text-gray-900 dark:text-white">On the map</strong>, <strong class="text-gray-900 dark:text-white">Approximate position</strong>, <strong class="text-gray-900 dark:text-white">Placed by hand</strong>, <strong class="text-gray-900 dark:text-white">Waiting</strong>, <strong class="text-gray-900 dark:text-white">No street address</strong>, <strong class="text-gray-900 dark:text-white">No country</strong>, <strong class="text-gray-900 dark:text-white">Address not found</strong> or <strong class="text-gray-900 dark:text-white">Off the map</strong>, with the venues that need attention first. A venue with a town and no street is placed at the centre of a small town or village and marked approximate. In a larger town it is left off, because the centre of a town would put it in the wrong place. To fix a venue, correct its address: a changed address is looked up again.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Fixing a pin yourself</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Each venue in the list has its own actions, and each is saved the moment you press it, apart from the form's Save. <strong class="text-gray-900 dark:text-white">Move pin</strong> opens a small map: drag the pin, click where the venue is, or move the map with the arrow keys and press <strong class="text-gray-900 dark:text-white">Put the pin at the centre of the map</strong>, then <strong class="text-gray-900 dark:text-white">Save position</strong>. <strong class="text-gray-900 dark:text-white">Place by hand</strong> is the same for a venue with no pin yet, such as one in a village with no street names, or one still waiting to be looked up. A pin you place stays where you put it, even if the venue's address is corrected later: <strong class="text-gray-900 dark:text-white">Use the looked-up position</strong> in that map goes back to what the address search found. <strong class="text-gray-900 dark:text-white">Take off the map</strong> removes a venue from your map and keeps it in the list, where <strong class="text-gray-900 dark:text-white">Put back on the map</strong> returns it. All of this is about your map only: it changes nothing on the venue itself or on another schedule's map. Moving and placing are offered where the operator has set up street images.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Seeing a venue's events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A venue's panel lists its next three events. Where it has more, <strong class="text-gray-900 dark:text-white">See all events here</strong> sets the venue filter of the event list below the map, the same filter a visitor can choose there, and changes no other filter. The button is offered when the list has an event at that venue under the filters the visitor already has, so in the month view it appears for venues with an event in the month on screen.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Visitor privacy</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The street images come from a map service, which sees the address of whoever loads them. A visitor who has allowed <strong class="text-gray-900 dark:text-white">Marketing and embedded content</strong> gets them when the map opens, which is on arrival if you set the map to start open. For anyone else nothing is loaded until they ask: the band says which service the images come from, and pressing <strong class="text-gray-900 dark:text-white">Show map</strong> beside that sentence loads them, each time the page is opened; <strong class="text-gray-900 dark:text-white">Open without streets</strong> shows the pins on a plain ground instead. The venue addresses are sent to the address search by the server, and nothing about a visitor is.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Availability</div>
            <p>This row only appears if the operator of your Event Schedule instance has set up a map service, and never on a venue schedule, which is one place. It is available on <strong class="text-gray-900 dark:text-white">all plans</strong>, including Free.</p>
        </div>

        <!-- Accommodation -->
        <h3 id="engagement-accommodation" class="doc-subheading">Accommodation</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Show a map of hotels and rentals near the venue on your public event pages, so attendees travelling in can find somewhere to stay without leaving your schedule. Bookings made through the map earn an affiliate commission.
        </p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Show nearby accommodation</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Off by default. Turn it on and an accommodation section appears on event pages whose venue has a validated address. Check-in and check-out dates are filled in from the event: a single evening becomes one night, and a multi-day event covers its full run.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Stay22 affiliate ID</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Add your own Stay22 affiliate ID to earn the commission from bookings on your pages. A Stay22 account is free. If you leave this blank, the commission goes to whoever runs this Event Schedule instance instead.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Venue address required</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The map centers on the venue's coordinates, so nothing appears for events whose venue has no validated address. It is also hidden for past events, embedded calendars, and shareable event graphics.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Visitor privacy</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The map is not loaded when the page opens. Visitors see a short explanation and a button, and nothing is requested from Stay22 until they either accept cookies or click to show the map. Visitors sending a Global Privacy Control signal are never shown it.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Availability</div>
            <p>This row only appears if the operator of your Event Schedule instance has enabled the integration. It is available on <strong class="text-gray-900 dark:text-white">all plans</strong>, including Free.</p>
        </div>
    </section>

    <!-- Gift Cards -->
    <section id="gift-cards" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
            </svg>
            Gift Cards <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong class="text-gray-900 dark:text-white">Gift Cards</strong> section sells gift cards that buyers send to someone by email and that are redeemed toward tickets for any event on the schedule. It opens on one switch, <strong class="text-gray-900 dark:text-white">Enable gift cards</strong>; the amounts, the currency, how long a card stays valid and the payment method appear once that is on. In the list of sections it says whether gift cards are on, and for which amounts.</p>
        <p class="text-gray-600 dark:text-gray-300">Gift cards have a guide of their own: see <a href="{{ route('marketing.docs.gift_cards') }}#setup" class="doc-link">setting up gift cards</a>.</p>
    </section>

    <!-- Auto Import -->
    <section id="auto-import" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
            Auto Import <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 ml-2">Selfhost</span>
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Point Event Schedule at a page that lists events and it reads them once a day, so a venue calendar or a tour page keeps your schedule current without you retyping anything. This section only exists on selfhosted installs, and it needs an AI key configured on the server.</p>

        <x-doc-screenshot id="creating-schedules--section-auto-import" alt="The Auto Import section on a selfhosted install: the Import URLs list and the Import Cities list, each with an Add link" />

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Import URLs</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">The addresses to read. Add as many as you like: venue event pages, artist tour pages, ticketing organizer pages, and most other sites that list events with a date. The page is fetched and the events on it are read out by AI.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">A site's <code class="doc-inline-code">robots.txt</code> is checked first, and a page it asks robots to leave alone is skipped.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Import Cities</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A filter, not a search. Name one or more cities and only events in those cities are taken from the URLs above; leave it empty to take everything. Cities on their own import nothing, because there is always a URL doing the actual reading.</p>
            </div>
        </div>

        <h3 class="doc-subheading">Setting up auto import</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Edit Schedule</strong> and choose <strong class="text-gray-900 dark:text-white">Auto Import</strong>.</li>
            <li>Under <strong class="text-gray-900 dark:text-white">Import URLs</strong>, click <strong class="text-gray-900 dark:text-white">+ Add</strong> and paste an address. Repeat for each source.</li>
            <li>Optionally add cities under <strong class="text-gray-900 dark:text-white">Import Cities</strong> to narrow what is taken.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Test Import</strong> to see what a source produces before you commit to it.</li>
            <li>Save. From then on the sources are re-read once a day.</li>
        </ol>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Auto-imported events go to your pending queue if you have <a href="#engagement-requests" class="doc-link">Require Approval</a> enabled, so you can review them before they appear publicly.</p>
        </div>
    </section>

    <!-- Integrations -->
    <section id="integrations" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z" />
            </svg>
            Integrations
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Connect your schedule to the outside world: send its email through your own server, keep it in step with a calendar you already use, and hand out feed URLs other apps can subscribe to. The rows are <strong class="text-gray-900 dark:text-white">Email Settings</strong> (hosted platform only), <strong class="text-gray-900 dark:text-white">Google Calendar</strong>, <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong>, <strong class="text-gray-900 dark:text-white">CalDAV Calendar</strong>, and <strong class="text-gray-900 dark:text-white">Calendar text and feeds</strong>. A row that is not connected says <strong class="text-gray-900 dark:text-white">Not connected</strong>; a connected calendar row says which way it syncs.</p>

        <x-doc-screenshot id="creating-schedules--section-integrations" alt="The Integrations section on a selfhosted install, which has no Email Settings row: Google Calendar, Outlook Calendar and CalDAV Calendar, each saying Not connected, and Calendar text and feeds" />

        <!-- Email -->
        <h3 id="integrations-email" class="doc-subheading">Email Settings</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Send this schedule's mail (ticket confirmations, notifications, feedback requests and <a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">newsletters</a>) through your own SMTP server and from your own address.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Per-schedule email settings are a hosted-platform feature, available on every plan, and the first row of Integrations there. Selfhosted installs configure mail once at the server level instead, so the row is not shown: see the <a href="{{ route('marketing.docs.selfhost.email') }}" class="doc-link">selfhost email docs</a>.</p>

        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4">Setting up custom email</h4>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Edit Schedule</strong>, choose <strong class="text-gray-900 dark:text-white">Integrations</strong>, and open the <strong class="text-gray-900 dark:text-white">Email Settings</strong> row.</li>
            <li>Fill in <strong class="text-gray-900 dark:text-white">SMTP Host</strong>, <strong class="text-gray-900 dark:text-white">SMTP Port</strong> and <strong class="text-gray-900 dark:text-white">Encryption</strong> (None, TLS or SSL) from your email provider.</li>
            <li>Enter the <strong class="text-gray-900 dark:text-white">SMTP Username</strong> and <strong class="text-gray-900 dark:text-white">SMTP Password</strong>. For Gmail or Google Workspace this must be an App Password, not your account password.</li>
            <li>Set the <strong class="text-gray-900 dark:text-white">From Address</strong> and <strong class="text-gray-900 dark:text-white">From Name</strong> your recipients will see, for example <code class="doc-inline-code">events@yourdomain.com</code>.</li>
            <li>Save, then click <strong class="text-gray-900 dark:text-white">Send Test Email</strong>. If it fails, the exact error from your provider is shown.</li>
        </ol>

        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 mt-8">Troubleshooting</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If a message fails to send, click <strong class="text-gray-900 dark:text-white">Send Test Email</strong> to see the exact error returned by your email provider. A <strong class="text-gray-900 dark:text-white">"permission denied"</strong> error almost always comes from the provider rejecting your credentials or sender address, not from Event Schedule. Most problems fall into one of these categories:</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Permission denied" or authentication errors</h4>
                <ul class="doc-list text-sm">
                    <li>Double-check your SMTP username and password.</li>
                    <li>For Gmail or Google Workspace, create an <x-link href="https://myaccount.google.com/apppasswords" target="_blank">App Password</x-link> and use that instead of your normal account password.</li>
                    <li>Make sure your account has SMTP access enabled with your provider.</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sender address rejected or not authorized</h4>
                <ul class="doc-list text-sm">
                    <li>The most common cause of a "permission denied" style rejection: your <strong class="text-gray-900 dark:text-white">From address</strong> must be a verified sender (or on a verified domain) with your provider.</li>
                    <li>Providers such as Amazon SES, SendGrid, Mailgun, and Postmark reject mail sent from an unverified address.</li>
                    <li>Amazon SES accounts in sandbox mode can only send to verified recipients until you request production access.</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Connection refused or timeout</h4>
                <ul class="doc-list text-sm">
                    <li>Use port <code class="doc-inline-code">587</code> with TLS, or port <code class="doc-inline-code">465</code> with SSL, and make sure the port and encryption match.</li>
                    <li>Confirm the SMTP host is spelled correctly and that your provider allows outbound SMTP.</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Emails going to spam</h4>
                <ul class="doc-list text-sm">
                    <li>Set up SPF, DKIM, and DMARC DNS records for your sending domain.</li>
                    <li>Use a From address on a domain you own rather than a free email provider.</li>
                </ul>
            </div>
        </div>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">When email settings stop working</div>
            <p>If your SMTP credentials start failing, the <strong class="text-gray-900 dark:text-white">Email Settings</strong> row and the Integrations entry in the list of sections both say <strong class="text-gray-900 dark:text-white">Email delivery is failing</strong>, and a warning appears inside the row, with a <strong class="text-gray-900 dark:text-white">Show error details</strong> link carrying the provider's own message. Delivery is paused while settings are failing; Event Schedule retries after 24 hours, or immediately once a test email succeeds. Fix the underlying problem, then send a test email to resume delivery right away.</p>
        </div>

        <!-- What the three calendar rows share -->
        <h3 id="calendar-sync" class="doc-subheading">Calendar sync</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The next three rows each keep the schedule in step with a calendar you already use. All three are <a href="{{ route('marketing.pricing') }}" class="doc-link">free on every plan</a> and work the same way: choose a calendar, choose a direction, save. A connection belongs to the schedule's owner, whose account it runs on, so the owner is the one who sets it.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sync Direction</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Four choices per integration: <strong class="text-gray-900 dark:text-white">To</strong> the calendar (push your events out), <strong class="text-gray-900 dark:text-white">From</strong> the calendar (pull its events in), <strong class="text-gray-900 dark:text-white">Bidirectional Sync</strong> (keep both in step), or <strong class="text-gray-900 dark:text-white">No Sync</strong>, which is where every integration starts. Closed, the row shows the direction you chose.</p>
            </div>
            <div id="delete-sync" class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">When an event is deleted in the connected calendar</h4>
                <p class="text-sm text-gray-600 dark:text-gray-300">Shown in the Google and Outlook rows once that integration is pulling events in; CalDAV has no such setting, so an event deleted there stays here. It is one setting for both: with Google connected it sits in the Google Calendar row, and otherwise in the Outlook Calendar row. Choose what happens here when you delete an event there: <strong class="text-gray-900 dark:text-white">Keep it here</strong> (the default), <strong class="text-gray-900 dark:text-white">Mark as cancelled</strong> (hidden but reversible), or <strong class="text-gray-900 dark:text-white">Delete it here</strong>. Deleting is permanent, so an event with any ticket sale on record (refunded ones included) or an ad boost that has spent money is hidden rather than deleted. An event that another schedule owns and shares with yours is only taken off your schedule, never deleted for everyone.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">A recurring event syncs as one entry</div>
            <p>Event Schedule does not send a repeat rule to a connected calendar, so a weekly event arrives there as a single entry on its start date. If you want every date to show up in someone's calendar app, give them the <a href="#integrations-advanced" class="doc-link">iCal feed</a> instead, which lists each occurrence.</p>
        </div>

        <!-- Google Calendar -->
        <h3 id="integrations-google" class="doc-subheading">Google Calendar</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Keep your schedule and a Google Calendar in step. Google tells Event Schedule about changes as they happen, so an edit made on either side shows up on the other without waiting for a poll.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Connect your Google account first, in <a href="{{ route('marketing.docs.account_settings') }}#google" class="doc-link">Account Settings</a>. Then:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Edit Schedule</strong>, choose <strong class="text-gray-900 dark:text-white">Integrations</strong>, and open the <strong class="text-gray-900 dark:text-white">Google Calendar</strong> row. Until your Google account is connected, the row holds a <strong class="text-gray-900 dark:text-white">Connect Google Calendar</strong> button in place of the settings.</li>
            <li>Pick the calendar to sync with under <strong class="text-gray-900 dark:text-white">Select Google Calendar</strong>.</li>
            <li>Choose a <strong class="text-gray-900 dark:text-white">Sync Direction</strong>: To Google Calendar, From Google Calendar, Bidirectional Sync, or No Sync. If you connected Google from the <a href="{{ route('marketing.docs.ai_import') }}#google-import" class="doc-link">import page</a>, the connection is read-only and the two choices that send events to Google are off until you click <strong class="text-gray-900 dark:text-white">Allow at Google</strong>.</li>
            <li>If you are pulling events in, set <a href="#delete-sync" class="doc-link">what happens when an event is deleted there</a>.</li>
            <li>Save. To run a sync by hand afterwards, the owner has <strong class="text-gray-900 dark:text-white">Sync Events</strong> in the <strong class="text-gray-900 dark:text-white">Actions</strong> menu at the top of the schedule's page.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The row also holds a <strong class="text-gray-900 dark:text-white">Resync to Google Calendar</strong> button, for when you have switched Google account or calendar. Save the form first, because it uses the saved calendar. It sends every event of the schedule to that calendar again and never imports anything from Google, so it needs a direction that sends events there. Copies of the events may be left behind on the calendar you used before.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A team member who is not the owner sees <strong class="text-gray-900 dark:text-white">Sync to My Calendar</strong> in this row instead, where they can point the schedule's events at a calendar of their own. That choice is theirs alone and is separate from the schedule-wide sync above, so everyone can follow the schedule in their own Google account. The schedule-wide sync itself is set by the schedule's owner, because it runs on the owner's Google account.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Selfhost note</div>
            <p>Google Calendar sync needs Google API credentials on the server. See the <a href="{{ route('marketing.docs.selfhost.google_calendar') }}" class="doc-link">selfhost Google Calendar docs</a> for setup instructions.</p>
        </div>

        <!-- Outlook / Microsoft Calendar -->
        <h3 id="integrations-microsoft" class="doc-subheading">Outlook Calendar</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The same two-way sync against an Outlook or Microsoft 365 calendar, over the Microsoft Graph API. Changes arrive near-instantly, with regular polling as a safety net.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Connect your Outlook account first, in <a href="{{ route('marketing.docs.account_settings') }}#microsoft" class="doc-link">Account Settings</a>. Then:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Edit Schedule</strong>, choose <strong class="text-gray-900 dark:text-white">Integrations</strong>, and open the <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong> row. Until your Outlook account is connected, the row holds a <strong class="text-gray-900 dark:text-white">Connect Outlook Calendar</strong> button in place of the settings.</li>
            <li>Pick the calendar to sync with.</li>
            <li>Choose a <strong class="text-gray-900 dark:text-white">Sync Direction</strong>: To Outlook Calendar, From Outlook Calendar, Bidirectional Sync, or No Sync.</li>
            <li>If you are pulling events in, set <a href="#delete-sync" class="doc-link">what happens when an event is deleted there</a>.</li>
            <li>Optionally turn on <strong class="text-gray-900 dark:text-white">Create Teams meetings for online events</strong> so an online event with no venue is created as a Microsoft Teams meeting and its join link is saved back onto the event.</li>
            <li>Save.</li>
        </ol>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Selfhost note</div>
            <p>Outlook Calendar sync needs an Azure app registration. See the <a href="{{ route('marketing.docs.selfhost.microsoft_calendar') }}" class="doc-link">selfhost Outlook Calendar docs</a> for setup instructions.</p>
        </div>

        <!-- CalDAV -->
        <h3 id="integrations-caldav" class="doc-subheading">CalDAV Calendar</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">CalDAV is the open standard behind Apple Calendar, Fastmail, Nextcloud and many others, so this row covers everything the two above do not. There is no webhook in the standard, so changes are picked up on a regular sweep rather than the moment they happen.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Edit Schedule</strong>, choose <strong class="text-gray-900 dark:text-white">Integrations</strong>, and open the <strong class="text-gray-900 dark:text-white">CalDAV Calendar</strong> row.</li>
            <li>Enter the <strong class="text-gray-900 dark:text-white">Server URL</strong>, <strong class="text-gray-900 dark:text-white">Username</strong> and <strong class="text-gray-900 dark:text-white">Password</strong>. Providers that use two-factor authentication usually want an app-specific password here rather than your account password.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Test Connection</strong>. Once it succeeds, the calendar list and the sync direction appear.</li>
            <li>Pick the calendar to sync with.</li>
            <li>Choose a <strong class="text-gray-900 dark:text-white">Sync Direction</strong>: To CalDAV, From CalDAV, or Bidirectional Sync.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Connect</strong>. The connection is saved at once and the page reloads, so save any other changes on the form first.</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Once connected, the row shows which server you are attached to, the sync direction can be changed there (with No Sync as a fourth choice), and <strong class="text-gray-900 dark:text-white">Disconnect</strong> asks you to confirm and then stops syncing. The connection belongs to the schedule's owner: other members see how it is set and cannot change it.
        </p>

        <!-- Feeds from other sites -->
        <h3 id="integrations-feeds" class="doc-subheading">Feeds from other sites</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A feed is an address this schedule reads about once an hour: a calendar address (.ics or webcal), an RSS, Atom or JSON feed, or a page that lists its events. New events arrive on their own, a change at the source is copied to the event here, and anything you edited yourself is kept as you left it. A connected calendar, above, is your own calendar and syncs both ways. A feed is somebody else's list, and is only ever read.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The row names the feeds the schedule has and leads to them. With none yet, <strong class="text-gray-900 dark:text-white">Add a feed</strong> asks for the address, reads it once, and shows what it found before anything is added. When you add it you choose whether new events are published at once or held as drafts for you to look over, and what happens to an event that is no longer in the feed: leave it, mark it cancelled, or remove it. An event that people have signed up for is never changed without asking you.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Once a schedule has a feed, a <strong class="text-gray-900 dark:text-white">Feeds</strong> tab appears on the schedule's own page, where drafts wait for review and each feed says when it was last read. On eventschedule.com feeds are part of the Enterprise plan. A selfhosted install has them. Everything a feed does is in <a href="{{ route('marketing.docs.managing_schedules') }}#feeds" class="doc-link">Managing Schedules: Feeds</a>.</p>

        <!-- Advanced -->
        <h3 id="integrations-advanced" class="doc-subheading">Calendar text and feeds</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">This row holds two things: the wording used for your events inside a connected calendar, and the read-only feed URLs for your schedule.</p>

        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Calendar Description Template</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            An event pushed out to Google Calendar, Outlook or CalDAV normally arrives carrying its own description and nothing else. Set a <strong class="text-gray-900 dark:text-white">Calendar Description Template</strong> and every outbound event uses your wording instead, built from the same variables as <a href="{{ route('marketing.docs.event_graphics') }}#variables" class="doc-link">event graphics</a>. Leave it empty and the event description is used as-is. <strong class="text-gray-900 dark:text-white">Show available variables</strong>, above the box, opens the <a href="#available-variables" class="doc-link">list below</a>.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            A line whose variables all come out empty is dropped rather than left as a stray separator, so one template can serve events with a venue and events without.
        </p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>A template</span>
            </div>
            <pre><code>{event_name}
{date_full_dmy} {time}
{venue} | {city}

{description}

{url}</code></pre>
        </div>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>What the calendar receives</span>
            </div>
            <pre><code>Summer Concert
15/03/2025 20:00
Central Park | New York

Join us for a night of music...

example.eventschedule.com/summer-concert</code></pre>
        </div>

        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Feeds</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Under the template, the row gives you two read-only addresses, each with a copy button, that let anyone follow your schedule from an app of their own. They list your public, upcoming events, need no login, and pick up your changes the next time the app checks them.</p>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">iCal Feed</strong>: subscribe from any calendar app (Google Calendar, Apple Calendar, Outlook). Unlike a connected calendar, this feed lists a recurring event on every date it falls in the next 90 days, not just the first.</li>
            <li><strong class="text-gray-900 dark:text-white">RSS Feed</strong>: follow your schedule from any RSS reader.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Visitors can find the calendar feed without you: <strong class="text-gray-900 dark:text-white">Subscribe to all events from</strong> your schedule's name appears in the Add to Calendar menu on an event page and in the sign-up panel. See <a href="{{ route('marketing.docs.sharing') }}#calendar-feeds" class="doc-link">calendar feeds</a>.</p>

        <h3 id="available-variables" class="doc-subheading">Available Variables</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The full list, shared by the calendar description template and by <a href="{{ route('marketing.docs.event_graphics') }}#variables" class="doc-link">event graphics</a>. A variable with nothing behind it, such as <code class="doc-inline-code">{venue}</code> on an online event, simply comes out empty.</p>

        <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Date & Time</h5>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">{day_name}</code></td>
                        <td>Full day name (translated)</td>
                        <td>Wednesday</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{day_short}</code></td>
                        <td>Short day name (translated)</td>
                        <td>Wed</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{date_dmy}</code></td>
                        <td>Day/month format (year added for other years)</td>
                        <td>15/3</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{date_mdy}</code></td>
                        <td>Month/day format (year added for other years)</td>
                        <td>3/15</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{date_full_dmy}</code></td>
                        <td>Full date (day/month/year)</td>
                        <td>15/03/2025</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{date_full_mdy}</code></td>
                        <td>Full date (month/day/year)</td>
                        <td>03/15/2025</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{month}</code></td>
                        <td>Month number</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{month_pad}</code></td>
                        <td>Month number (zero-padded)</td>
                        <td>03</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{month_name}</code></td>
                        <td>Full month name (translated)</td>
                        <td>March</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{month_short}</code></td>
                        <td>Short month name (translated)</td>
                        <td>Mar</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{day}</code></td>
                        <td>Day of month</td>
                        <td>15</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{day_pad}</code></td>
                        <td>Day of month (zero-padded)</td>
                        <td>05</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{year}</code></td>
                        <td>Year</td>
                        <td>2025</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{time}</code></td>
                        <td>Start time (uses schedule's 24h setting)</td>
                        <td>20:00 or 8:00 PM</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{end_time}</code></td>
                        <td>End time (uses schedule's 24h setting)</td>
                        <td>22:00 or 10:00 PM</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{duration}</code></td>
                        <td>Duration in hours</td>
                        <td>2</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Event Information</h5>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">{event_name}</code></td>
                        <td>Event Name</td>
                        <td>Summer Concert</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{short_description}</code></td>
                        <td>Short Description</td>
                        <td>Live jazz with local artists</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{description}</code></td>
                        <td>Description</td>
                        <td>Join us for a night of music...</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{url}</code></td>
                        <td>Event URL</td>
                        <td>https://...</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{number}</code></td>
                        <td>Position in the list, on event graphics only (empty in a calendar description)</td>
                        <td>3</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Venue Information</h5>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">{venue}</code></td>
                        <td>Venue name (translated)</td>
                        <td>Central Park</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{city}</code></td>
                        <td>City</td>
                        <td>New York</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{address}</code></td>
                        <td>Street address</td>
                        <td>123 Main St</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{state}</code></td>
                        <td>State/Province</td>
                        <td>NY</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{country}</code></td>
                        <td>Country</td>
                        <td>US</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-2"><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Ticket</a> Information</h5>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">{currency}</code></td>
                        <td>Currency code</td>
                        <td>USD</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{price}</code></td>
                        <td>Lowest ticket price. Empty when the event is free</td>
                        <td>10</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{coupon_code}</code></td>
                        <td>Event coupon code</td>
                        <td>SAVE20</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{coupon_discount}</code></td>
                        <td>What the coupon is worth, as a percentage or an amount in the event's currency. Empty when no discount is set</td>
                        <td>15%</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{discounted_price}</code></td>
                        <td>The price with the coupon taken off, as a bare figure with no currency symbol. Empty when the event has no discount or no price, and on any event that sells tickets or takes RSVPs through the platform</td>
                        <td>119</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{original_price}</code></td>
                        <td>The price before the discount. The same price as <code class="doc-inline-code">{price}</code>, written to the currency's own decimal places and with a thousands separator, but empty unless a discount applies, so a before-and-after pair appears together or not at all</td>
                        <td>149</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Custom Fields <x-doc-badge plan="pro" /></h5>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            If you have defined <a href="{{ marketing_url('/features/custom-fields') }}" class="doc-link">Event Custom Fields</a> in your schedule settings, you can include their values using numbered variables.
        </p>

        @if (!empty($customFieldsData))
            @foreach ($customFieldsData as $scheduleData)
                <h6 class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ $scheduleData['role_name'] }}</h6>
                <div class="doc-table-wrap">
                    <table class="doc-table">
                        <thead>
                            <tr>
                                <th>Variable</th>
                                <th>Field Name</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($scheduleData['fields'] as $index => $field)
                            <tr>
                                <td><code class="doc-inline-code">{custom_{{ $field['index'] ?? $loop->iteration }}}</code></td>
                                <td>{{ $field['name'] }}</td>
                                <td>{{ __('messages.type_'.($field['type'] ?? 'string')) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @else
            <div class="doc-table-wrap">
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Description</th>
                            <th>Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code class="doc-inline-code">{custom_1}</code></td>
                            <td>Value of the 1st custom field</td>
                            <td>John Smith</td>
                        </tr>
                        <tr>
                            <td><code class="doc-inline-code">{custom_2}</code></td>
                            <td>Value of the 2nd custom field</td>
                            <td>Room 101</td>
                        </tr>
                        <tr>
                            <td><code class="doc-inline-code">{custom_3}</code></td>
                            <td>Value of the 3rd custom field</td>
                            <td>Workshop</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-gray-400 text-sm">...up to {custom_10}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Header and footer text use a different set</div>
            <p>Every variable above belongs to one event. The schedule-wide <strong class="text-gray-900 dark:text-white">header</strong> and <strong class="text-gray-900 dark:text-white">footer text</strong> on <a href="{{ route('marketing.docs.event_graphics') }}#header-footer-text" class="doc-link">event graphics</a> have no event behind them, so they take a smaller, context-free set instead: <code class="doc-inline-code">{schedule_name}</code>, today's date parts such as <code class="doc-inline-code">{month_name}</code> and <code class="doc-inline-code">{year}</code>, and the range covered by the graphic, <code class="doc-inline-code">{first_event_date}</code> and <code class="doc-inline-code">{last_event_date}</code>. The event graphics guide lists them all.</p>
        </div>
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
            <li><a href="{{ route('marketing.docs.schedule_styling') }}" class="doc-link">Schedule Styling</a> - Colors, fonts, backgrounds, and visual customization</li>
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - Add events to your schedule</li>
            <li><a href="{{ route('marketing.docs.sharing') }}" class="doc-link">Sharing Your Schedule</a> - Embed and share your schedule</li>
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - Set up ticketing for your events</li>
            <li><a href="{{ route('marketing.docs.managing_schedules') }}" class="doc-link">Managing Schedules</a> - View events, manage team, set availability, and more</li>
        </ul>
    </section>


    <x-slot:schema>
        <script type="application/ld+json" {!! nonce_attr() !!}>
        {
            "@context": "https://schema.org",
            "@type": "HowTo",
            "name": "How to Create and Configure Your Event Schedule",
            "description": "Set up your schedule with details, address, contact info, settings, sub-schedules, auto import, and calendar integrations.",
            "totalTime": "PT10M",
            "step": [
                {
                    "@type": "HowToStep",
                    "name": "Choose Your Schedule Type",
                    "text": "Select the appropriate schedule type: Talent for performers, Venue for event spaces, or Curator for promoters.",
                    "url": "{{ url(route('marketing.docs.creating_schedules')) }}#schedule-types"
                },
                {
                    "@type": "HowToStep",
                    "name": "Enter Schedule Details",
                    "text": "Choose the schedule in the sidebar and click Edit Schedule, then set your schedule name, short description, and description, which supports Markdown formatting.",
                    "url": "{{ url(route('marketing.docs.creating_schedules')) }}#details"
                },
                {
                    "@type": "HowToStep",
                    "name": "Set Your Address",
                    "text": "For Venue schedules, add your full address including street, city, state, postal code, and country, so your event pages can show it on a map.",
                    "url": "{{ url(route('marketing.docs.creating_schedules')) }}#address"
                },
                {
                    "@type": "HowToStep",
                    "name": "Configure Settings",
                    "text": "Set your schedule URL, choose your email notifications and whether subscribers get a digest of new events, and set defaults such as the event URL pattern and the visibility new events start with.",
                    "url": "{{ url(route('marketing.docs.creating_schedules')) }}#settings"
                },
                {
                    "@type": "HowToStep",
                    "name": "Set Up Auto Import",
                    "text": "On a selfhosted install, list the web pages to read events from once a day, and optionally the cities to keep.",
                    "url": "{{ url(route('marketing.docs.creating_schedules')) }}#auto-import"
                },
                {
                    "@type": "HowToStep",
                    "name": "Connect Calendar Integrations",
                    "text": "Sync with Google Calendar, Outlook Calendar, or CalDAV so your events stay in step with the calendar you already use.",
                    "url": "{{ url(route('marketing.docs.creating_schedules')) }}#integrations"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
