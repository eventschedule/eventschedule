<x-docs-page
    key="creating-schedules"
    description="Learn how to create and configure your schedule in Event Schedule. Set up details, address, contact info, sub-schedules, auto import, and calendar sync."
    lede="Set up and configure your schedule - from basic details and contact info to sub-schedules, calendar integrations, and auto import."
    article-description="Learn how to create and configure your schedule in Event Schedule. Set up details, address, contact info, settings, sub-schedules, auto import, calendar integrations, and more."
>
    <x-slot:toc>
        <x-doc-nav-link href="#schedule-types">Schedule Types</x-doc-nav-link>
        <x-doc-nav-group label="Details" href="#details">
            <x-doc-nav-link href="#details-general">General</x-doc-nav-link>
            <x-doc-nav-link href="#details-localization">Localization</x-doc-nav-link>
            <x-doc-nav-link href="#contact-info">Contact Info</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#ai-details-generator">AI Details Generator</x-doc-nav-link>
        <x-doc-nav-link href="#address">Address</x-doc-nav-link>
        <x-doc-nav-link href="#merge">Merging Duplicates</x-doc-nav-link>
        <x-doc-nav-link href="#style">Style</x-doc-nav-link>
        <x-doc-nav-link href="#videos-links">Videos & Links</x-doc-nav-link>
        <x-doc-nav-group label="Customize" href="#customize">
            <x-doc-nav-link href="#customize-subschedules">Sub-schedules</x-doc-nav-link>
            <x-doc-nav-link href="#customize-custom-fields">Custom Fields</x-doc-nav-link>
            <x-doc-nav-link href="#customize-categories">Custom Categories</x-doc-nav-link>
            <x-doc-nav-link href="#customize-custom-labels">Custom Labels</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Settings" href="#settings">
            <x-doc-nav-link href="#settings-general">General</x-doc-nav-link>
            <x-doc-nav-link href="#custom-domain">Custom Domain</x-doc-nav-link>
            <x-doc-nav-link href="#settings-notifications">Notifications</x-doc-nav-link>
            <x-doc-nav-link href="#settings-advanced">Advanced</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Engagement" href="#engagement">
            <x-doc-nav-link href="#engagement-requests">Requests</x-doc-nav-link>
            <x-doc-nav-link href="#engagement-fan-content">Fan Content</x-doc-nav-link>
            <x-doc-nav-link href="#engagement-feedback">Feedback</x-doc-nav-link>
            <x-doc-nav-link href="#engagement-carpool">Carpool</x-doc-nav-link>
            <x-doc-nav-link href="#engagement-sponsors">Sponsors</x-doc-nav-link>
            <x-doc-nav-link href="#engagement-accommodation">Accommodation</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#event-sources">Event Sources</x-doc-nav-link>
        <x-doc-nav-link href="#auto-import">Auto Import</x-doc-nav-link>
        <x-doc-nav-group label="Integrations" href="#integrations">
            <x-doc-nav-link href="#integrations-email">Email Settings</x-doc-nav-link>
            <x-doc-nav-link href="#integrations-google">Google Calendar</x-doc-nav-link>
            <x-doc-nav-link href="#integrations-microsoft">Outlook Calendar</x-doc-nav-link>
            <x-doc-nav-link href="#integrations-caldav">CalDAV Calendar</x-doc-nav-link>
            <x-doc-nav-link href="#integrations-advanced">Advanced</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Schedule Types -->
    <section id="schedule-types" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            Schedule Types
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule supports three types of schedules, each designed for different use cases:</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Best For</th>
                        <th>Key Features</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Talent</span></td>
                        <td>Musicians, DJs, performers, speakers</td>
                        <td>Events display venues, focused on "where you'll be"</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Venue</span></td>
                        <td>Bars, clubs, theaters, event spaces</td>
                        <td>Full address support, map integration</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Curator</span></td>
                        <td>Promoters, bloggers, community organizers</td>
                        <td>Aggregate events from multiple sources</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mt-6">The type is chosen when you create the schedule and it changes which sections the edit page offers. Only <strong class="text-gray-900 dark:text-white">Venue</strong> schedules get the <a href="#address" class="doc-link">Address</a> section and the <a href="#merge" class="doc-link">Merge Venue</a> tool; only <strong class="text-gray-900 dark:text-white">Curator</strong> schedules get the per-schedule banner for the bulk <a href="#merge" class="doc-link">Merge Duplicate Venues</a> page, which is otherwise reached from Following; and <strong class="text-gray-900 dark:text-white">Talent</strong> schedules see a shorter <a href="#engagement-requests" class="doc-link">Requests</a> tab, because a booking request to a performer is always reviewed by hand.</p>
    </section>

    <!-- Details -->
    <section id="details" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            Details
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Everything on this page lives on one form. To reach it, open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule</strong> and click <strong class="text-gray-900 dark:text-white">Edit Schedule</strong>. The form is divided into sections listed down the left, and a single <strong class="text-gray-900 dark:text-white">Save</strong> button at the bottom of that list saves the whole form, whichever section you are in. Not every section is offered to every schedule:</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Covers</th>
                        <th>Shown</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><a href="#details" class="doc-link">Details</a></td>
                        <td>Name, description, language, timezone, contact details</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><a href="#address" class="doc-link">Address</a></td>
                        <td>Street address and map coordinates</td>
                        <td>Venue schedules</td>
                    </tr>
                    <tr>
                        <td><a href="#style" class="doc-link">Style</a></td>
                        <td>Colors, fonts, images, layout</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><a href="#videos-links" class="doc-link">Videos &amp; Links</a></td>
                        <td>YouTube videos and social profiles</td>
                        <td>After the first save</td>
                    </tr>
                    <tr>
                        <td><a href="#customize" class="doc-link">Customize</a></td>
                        <td>Sub-schedules, custom fields, categories, labels</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><a href="#settings" class="doc-link">Settings</a></td>
                        <td>URL, notifications, and advanced behavior</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><a href="#engagement" class="doc-link">Engagement</a></td>
                        <td>Requests, fan content, feedback, carpool, sponsors, accommodation</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><a href="{{ route('marketing.docs.gift_cards') }}" class="doc-link">Gift Cards</a></td>
                        <td>Selling gift cards for your events</td>
                        <td>After the first save</td>
                    </tr>
                    <tr>
                        <td><a href="#auto-import" class="doc-link">Auto Import</a></td>
                        <td>Pulling events in from other websites</td>
                        <td>Selfhosted installs</td>
                    </tr>
                    <tr>
                        <td><a href="#integrations" class="doc-link">Integrations</a></td>
                        <td>Email, calendar sync, templates, feeds</td>
                        <td>Always</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6"><a href="#merge" class="doc-link">Merge Venue</a> is an eleventh section, shown on an unclaimed Venue schedule when you manage at least one other venue it could be folded into.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The <strong class="text-gray-900 dark:text-white">Details</strong> section holds your schedule's core identity, on three tabs: General, Localization, and Contact Info.</p>

        <x-doc-screenshot id="creating-schedules--section-details" alt="Schedule details settings" loading="eager" />

        <!-- General Tab -->
        <h3 id="details-general" class="doc-subheading">General</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule Name</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Required. Your schedule's display name, shown at the top of your schedule page and in search results. Use your band name, venue name, or organization name.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Short Description</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A brief subtitle for your schedule, up to 200 characters. This appears below your schedule name on the schedule page.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Description</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A bio or description of your schedule. Supports <strong class="text-gray-900 dark:text-white">Markdown formatting</strong> for links, bold text, lists, and more. Tell visitors what you're about and what kind of events they can expect.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Translated name and description</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Once <a href="#details-localization" class="doc-link">a second language</a> is switched on and a translation exists, extra fields appear next to the name, short description and banner so you can correct the wording by hand. Each is labelled with the target language, for example <strong class="text-gray-900 dark:text-white">Name (English)</strong>, so it follows whichever language you chose to translate into rather than always being English.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Show banner <x-doc-badge plan="pro" /></h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Turn on <strong class="text-gray-900 dark:text-white">Show banner</strong> to display a message in a banner at the top of your schedule's guest page, such as a venue change or a "tickets on sale" notice. The <strong class="text-gray-900 dark:text-white">Banner message</strong> box takes up to 500 characters and accepts Markdown, including links. <strong class="text-gray-900 dark:text-white">Show on event pages too</strong> extends the banner from the schedule page to individual event pages.</p>
            </div>
        </div>

        <!-- Localization Tab -->
        <h3 id="details-localization" class="doc-subheading">Localization</h3>
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
    </section>

    <!-- AI Details Generator -->
    <section id="ai-details-generator" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
            </svg>
            AI Details Generator <x-doc-badge plan="enterprise" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Let AI write your schedule's <strong class="text-gray-900 dark:text-white">short description</strong> and <strong class="text-gray-900 dark:text-white">description</strong> from its name and type. Those two fields are all it generates.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>On the <strong class="text-gray-900 dark:text-white">Details</strong> section, click <strong class="text-gray-900 dark:text-white">AI Generator</strong> in the section header.</li>
            <li>Tick the fields you want. A field that already has content is left unticked and marked with a blue dot; tick it anyway and a warning tells you its content will be replaced.</li>
            <li>Optionally add <strong class="text-gray-900 dark:text-white">Additional instructions</strong> (up to 500 characters) to steer the tone, and tick <strong class="text-gray-900 dark:text-white">Save as default</strong> to reuse them next time.</li>
            <li>Optionally open <strong class="text-gray-900 dark:text-white">View and edit prompt</strong> to see the prompt that will be sent and adjust it for this run.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Generate</strong>, review the preview, and apply it. Nothing is written into the form until you accept, and nothing is saved until you save the schedule.</li>
        </ol>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Requirements</div>
            <p>The button only appears when the site has an AI key configured. Selfhosted installations need a <x-link href="https://ai.google.dev/" target="_blank">Gemini API key</x-link> in the environment settings, because this feature is generated by Gemini. On the hosted platform there is a daily cap on AI text generation per schedule; you are told if you reach it.</p>
        </div>
    </section>

    <!-- Address -->
    <section id="address" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
            Address
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The <strong class="text-gray-900 dark:text-white">Address</strong> section appears on <strong class="text-gray-900 dark:text-white">Venue</strong> schedules only. Filling it in puts a map on your schedule page, lets event pages show directions, and is what the <a href="#engagement-accommodation" class="doc-link">nearby accommodation</a> map centres on. Talent and Curator schedules give their location on the <a href="#contact-info" class="doc-link">Contact Info</a> tab instead.</p>

        <x-doc-screenshot id="creating-schedules--section-address" alt="Schedule address settings" />

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
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">View Map</strong> opens what you have typed on a map so you can check it landed in the right place. <strong class="text-gray-900 dark:text-white">Validate Address</strong> looks the address up and offers a tidied version of each field; click <strong class="text-gray-900 dark:text-white">Accept</strong> to take it. Validate Address only appears when the site has a Google Maps key configured.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Coordinates are worked out when you save</div>
            <p>Saving a new or changed address looks up its coordinates in the background, and those coordinates are what the map on your schedule page uses. Clearing the address clears them again. This step needs a Google Maps key on the site, so a selfhosted install without one keeps the address as text and shows no map.</p>
        </div>
    </section>

    <!-- Contact Info -->
    <section id="contact-info" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
            </svg>
            Contact Info
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Add contact details in the <strong class="text-gray-900 dark:text-white">Details &rarr; Contact Info</strong> tab so visitors can reach you. These appear on your public schedule page.</p>

        <x-doc-screenshot id="creating-schedules--section-contact-info" alt="Schedule contact information settings" />

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Email</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Required. This is the schedule's own address: it receives the notifications you turn on, and it has to be verified before the schedule's guest page can be opened. Turn on <strong class="text-gray-900 dark:text-white">Show email address</strong> to publish it to visitors as well; leave it off and the address stays private to you.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Phone number</h4>
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

    <!-- Merge -->
    <section id="merge" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
            </svg>
            Merging duplicates
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Importing events tends to create the same venue twice, once as "The Anchor" and again as "Anchor Bar". Rather than leave your calendar pointing at two half-empty pages, merge them. Every event moves to the schedule you keep and the duplicate goes away.</p>

        <h3 class="doc-subheading">Merge Venue</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A <strong class="text-gray-900 dark:text-white">Merge Venue</strong> section appears on the edit page when a schedule looks mergeable. The <strong class="text-gray-900 dark:text-white">Merge into</strong> dropdown lists the other venues you manage; pick one and confirm. All of this schedule's events move to the target and this one is removed. If Event Schedule spots a likely match by name, city and country, it names it for you above the dropdown, so usually you only have to confirm.</p>
        <div class="doc-callout mb-6">
            <div class="doc-callout-title">Only unclaimed schedules can be merged</div>
            <p>Merging is offered for schedules nobody has claimed yet, which is exactly the kind an import creates. Once someone claims a schedule it has a real operator behind it, so it can no longer be absorbed into another. Both schedules must also be the same type, so a venue merges into a venue and never into a talent.</p>
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
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 3 3 0 005.78-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
            </svg>
            Style
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Customize your schedule's visual appearance including colors, fonts, backgrounds, and layout. See the full <a href="{{ route('marketing.docs.schedule_styling') }}" class="doc-link">Schedule Styling</a> guide for all customization options.</p>
    </section>

    <!-- Videos & Links -->
    <section id="videos-links" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
            Videos & Links
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Two tabs, both shown on your public schedule page. The section appears once the schedule has been saved for the first time.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">YouTube Videos</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Paste a YouTube link and it is added with its thumbnail and title. Videos appear in a panel on your schedule page, which the <a href="#settings-advanced" class="doc-link">Hide Videos</a> setting can turn off for Venue and Curator schedules.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Social Links</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Add profile URLs (Instagram, Facebook, X, TikTok, Bandcamp, Spotify and so on) so visitors can find you elsewhere. The platform is recognised from the URL and its icon is used automatically.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Short links</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Every social link can have a short forwarding address under your own schedule URL, for example <code class="doc-inline-code">yourname.eventschedule.com/instagram</code>, with a copy button next to it. Links on a recognised platform get one automatically. For anything else, and for any link you want a nicer address for, click <strong>Add</strong> or <strong>Edit</strong> under the link and choose your own, so a ticketing partner at <code class="doc-inline-code">example.com/?ref=33221</code> becomes <code class="doc-inline-code">yourname.eventschedule.com/tickets</code>. Short links are handy in printed material and bios, they keep working if you change the underlying profile URL, and clicks on them are counted in <a href="{{ route('marketing.docs.analytics') }}" class="doc-link">Analytics</a>.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Adding your own address never replaces the automatic one, so a link already printed on a poster keeps working. A short link cannot reuse the name of another platform, of a <a href="#customize-subschedules" class="doc-link">sub-schedule</a>, or of a page the app already uses. Clear the box to remove it.</p>
            </div>
        </div>
    </section>

    <!-- Customize -->
    <section id="customize" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
            </svg>
            Customize
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The <strong class="text-gray-900 dark:text-white">Customize</strong> section has four tabs: Sub-schedules, Custom Fields, Categories, and Custom Labels.</p>

        <h3 id="customize-subschedules" class="doc-subheading">Sub-schedules</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Sub-schedules are named strands within one schedule, such as "Live Music", "DJ Nights", "Comedy" or "Workshops". Each one gets an address of its own and a color, so visitors can filter your calendar down to the strand they care about.</p>

        <x-doc-screenshot id="creating-schedules--section-subschedules" alt="Sub-schedules settings" />

        <h3 class="doc-subheading">Creating a sub-schedule</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open the <strong class="text-gray-900 dark:text-white">Customize</strong> section and stay on the <strong class="text-gray-900 dark:text-white">Sub-schedules</strong> tab.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">+ Add sub-schedule</strong> and give it a <strong class="text-gray-900 dark:text-white">Name</strong>. If your schedule is not written in English, an <strong class="text-gray-900 dark:text-white">English Name</strong> field appears beneath it; leave it blank and the translation fills it in.</li>
            <li>Pick a <strong class="text-gray-900 dark:text-white">Color</strong> from the 14-color palette, or use <strong class="text-gray-900 dark:text-white">Clear</strong> to leave it uncolored. The color is what distinguishes sub-schedules in calendar views and on the filter buttons.</li>
            <li>Save. The sub-schedule now has an address such as <code class="doc-inline-code">yourname.eventschedule.com/live-music</code>, shown with a copy button. <strong class="text-gray-900 dark:text-white">Edit</strong> changes that last part.</li>
        </ol>

        <div class="doc-callout mb-6">
            <div class="doc-callout-title">A sub-schedule sorts, it does not hide</div>
            <p>A sub-schedule carries a name, a color and an address, and nothing else. There is no visibility switch on one, so filing an event under a sub-schedule never hides it. To keep an event off your public page, set the event's own visibility instead - see <a href="{{ route('marketing.docs.creating_events') }}#draft" class="doc-link">event visibility</a>.</p>
        </div>

        <h3 class="doc-subheading">Assigning events to sub-schedules</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">When you create or edit an event, pick a sub-schedule from the dropdown. An event sits in one sub-schedule per schedule, so an event you also share to a Curator schedule can be filed under one of your strands and one of theirs.</p>

        <!-- Custom Fields -->
        <h3 id="customize-custom-fields" class="doc-subheading">Custom Fields <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Define <a href="{{ marketing_url('/features/custom-fields') }}" class="doc-link">Event Custom Fields</a> to add extra data to your events. You can add up to 10 custom fields per schedule. Custom field values can also be used as URL pattern variables.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Each custom field has the following properties:</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Field Name & English Name</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The display name for the field. For non-English schedules, an English name field also appears for translation.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Field Type</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Choose from six types: <strong class="text-gray-900 dark:text-white">String</strong> (single-line text), <strong class="text-gray-900 dark:text-white">Multiline String</strong> (multi-line text), <strong class="text-gray-900 dark:text-white">Switch</strong> (on/off toggle), <strong class="text-gray-900 dark:text-white">Date</strong> (date picker), <strong class="text-gray-900 dark:text-white">Dropdown</strong> (single select from predefined options), or <strong class="text-gray-900 dark:text-white">Multiselect</strong> (multiple selections from predefined options).</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Options</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">For <strong class="text-gray-900 dark:text-white">Dropdown</strong> and <strong class="text-gray-900 dark:text-white">Multiselect</strong> fields, type the choices separated by commas. The field only appears for those two types.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI Prompt</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">An optional instruction, up to 500 characters, telling the AI how to extract this field's value when a visitor submits an event through <a href="{{ route('marketing.docs.ai_import') }}" class="doc-link">AI Import</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Required</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Mark a field as required so that events cannot be saved without providing a value.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Private</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Hide the field's value from the guest portal. The value still appears in the admin portal and can be referenced in graphic templates and slug patterns via <code class="doc-inline-code">{custom_N}</code>. Public dropdown and multiselect fields become guest-portal filters; mark them private to remove the filter chip.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">On Request Form</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ask the field as a question on your public <a href="#engagement-requests" class="doc-link">event request form</a>, so visitors answer it when they submit an event. On by default. Uncheck it to keep a field for your own use in the admin portal. Combine a <strong class="text-gray-900 dark:text-white">Multiselect</strong> field with this to offer a checklist, for example which of your equipment the visitor needs.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Validation Pattern</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">For <strong class="text-gray-900 dark:text-white">String</strong> and <strong class="text-gray-900 dark:text-white">Multiline String</strong> fields you can require entries to match a pattern, such as a reference code or a phone number. Pick one of the ready-made patterns (email address, phone number, web address, numbers only, letters and numbers) or write your own regular expression, and use the built-in tester to try a sample value before saving. The optional <strong class="text-gray-900 dark:text-white">Hint</strong> is shown under the field so visitors know what format you expect. Patterns are enforced both in the browser and on the server.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Reordering</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Drag and drop fields to change the order they appear on event forms.</p>
            </div>
        </div>

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
                                <td><code class="doc-inline-code">{custom_{{ $loop->iteration }}}</code></td>
                                <td>{{ $field['name'] }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $field['type'] ?? 'string')) }}</td>
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

        <!-- Custom Categories -->
        <h3 id="customize-categories" class="doc-subheading">Custom Categories</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Tailor the event categories shown on your event form and your schedule's filter. The 12 system defaults (Art &amp; Culture, Business Networking, Community, Concerts, Education, Food &amp; Drink, Health &amp; Fitness, Parties &amp; Festivals, Personal Growth, Sports, Spirituality, Tech) are pre-loaded as editable rows. Rename or remove any of them, and add categories that match how you organise events, up to 32 rows in total. A category name can be up to 80 characters.
        </p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Renaming a default</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Edit the name field on any default row to change how it appears on your schedule. The old name is shown underneath for reference. Existing events tagged with that category automatically display the new name once you save.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Removing a default</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click the X on any default to remove it from your event form and guest portal filter. Events already tagged with that category keep their badge - the original name still resolves via the system defaults. If the category is in use, you'll see a confirmation showing how many events are affected. Use "Reset to default categories" to restore the original 12.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Adding a custom category</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click "+ Add category" and enter a name. Custom categories appear immediately in your event form and on the schedule filter.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Color</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Each category can be assigned a color from the 14-color palette. The category's color appears as a small dot beside every event tagged with that category on your schedule, making event types easy to scan at a glance. Categories without a color fall back to the sub-schedule color if one is set.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Ordering</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Categories are always shown alphabetically - there's no manual reordering. The list re-sorts itself when you rename, add, or remove a row.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Translation</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">If your schedule <a href="#details-localization" class="doc-link">offers a second language</a>, custom category names and renamed defaults are translated into it in the background by the same pipeline that translates the rest of your content. Visitors reading in that language see the translated name; everyone else sees the source name you typed.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Reset to defaults</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Removes all customisations and restores the original 12 categories. Existing events keep the category they were assigned.</p>
            </div>
        </div>
        <div class="doc-callout doc-callout-info mt-4">
            <div class="doc-callout-title">Cross-schedule viewing</div>
            <p>When a curator schedule shares an event from a talent schedule, the event's category badge shows the original name chosen by the talent. The curator's filter dropdown dynamically includes any categories present in visible events, so foreign categories remain filterable.</p>
        </div>

    </section>

    <!-- Custom Labels -->
    <section>
        <h3 id="customize-custom-labels" class="doc-subheading">Custom Labels <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Override the wording Event Schedule uses on your public schedule page. For example, change "Events" to "Shows", "Follow" to "Subscribe", or "Free entry" to "No cover charge".
        </p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Adding a custom label</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pick a label from the searchable dropdown and click <strong class="text-gray-900 dark:text-white">Add</strong>, then type your replacement, up to 200 characters. A label already overridden drops out of the dropdown, and <strong class="text-gray-900 dark:text-white">Remove</strong> puts it back and restores the original wording.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Available labels</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">34 labels in all, covering the buttons (Request to Book, Submit Event, Follow, Buy Tickets, Book a Time, Register, Share), the navigation (Events, Filter Events, Past Events, Load More, Show All), and the wording on events themselves (Free entry, Online, Schedule, Category, Venue, Agenda, About, Photo Gallery, Our Sponsors).</p>
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
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Settings
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The <strong class="text-gray-900 dark:text-white">Settings</strong> section controls how your schedule behaves, on three tabs: General, Notifications, and Advanced.</p>

        <x-doc-screenshot id="creating-schedules--section-settings" alt="Schedule settings" />

        <!-- General Tab -->
        <h3 id="settings-general" class="doc-subheading">General</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule URL</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your schedule's address, shown with a copy button. Click <strong class="text-gray-900 dark:text-white">Edit</strong> to change it. On the hosted platform it is a subdomain, <code class="doc-inline-code">yourname.eventschedule.com</code>; on a selfhosted install it is a path, <code class="doc-inline-code">yoursite.com/yourname</code>. Between 4 and 50 characters, lowercase letters, numbers and dashes only. Choose something memorable and easy to type, because changing it later breaks any link people have already saved.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Custom Domain <x-doc-badge plan="enterprise" /></h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Use your own domain, for example <code class="doc-inline-code">events.yourbrand.com</code>, instead of a subdomain. A custom domain gives your <a href="{{ route('marketing.docs.sharing') }}#schedule-url" class="doc-link">shared schedule URL</a> a more professional look.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">Edit</strong> under Schedule URL and the three choices appear: <strong class="text-gray-900 dark:text-white">Subdomain</strong> (the default, no custom domain), <strong class="text-gray-900 dark:text-white">Direct</strong>, and <strong class="text-gray-900 dark:text-white">Redirect</strong>. The last two need Enterprise, and they are offered on the hosted platform only. See the <a href="#custom-domain" class="doc-link">setup instructions</a> below.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Event URL Pattern</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Decides the address given to each new event. Leave it empty and the event name is used. Otherwise build a pattern from the <a href="#url-pattern-variables" class="doc-link">variables below</a>, for example <code class="doc-inline-code">{event_name}-{date_dmy}</code>, which produces addresses like <code class="doc-inline-code">my-event-27-1</code>.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Changing the pattern only affects events created from then on, so a button appears offering to apply it to your existing events as well.</p>
            </div>
        </div>

        <!-- URL Pattern Variables -->
        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4" id="url-pattern-variables">URL Pattern Variables</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Use these in the Event URL Pattern above. Every value is converted to something safe for a URL: lowercase, with spaces and punctuation turned into dashes. These are close cousins of the <a href="#available-variables" class="doc-link">calendar description variables</a> but not identical, because a URL cannot carry a slash or a colon: dates here use dashes and never gain a year, and there is no variable for the description or the event's own link.
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
            <li>In your schedule settings, enter your domain (e.g., <code class="doc-inline-code">events.yourbrand.com</code>) and select <strong class="text-gray-900 dark:text-white">Direct</strong>.</li>
            <li>Go to your domain registrar (e.g., GoDaddy, Namecheap, Cloudflare) and create a <strong class="text-gray-900 dark:text-white">CNAME record</strong> pointing your domain to <code class="doc-inline-code">{{ config('services.digitalocean.app_hostname') }}</code>.</li>
            <li>Wait for DNS propagation (usually a few minutes, but can take up to 48 hours).</li>
            <li>SSL is provisioned automatically. Once DNS has propagated, your schedule will be accessible at your custom domain over HTTPS.</li>
        </ol>

        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Redirect Mode (Cloudflare)</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Your custom domain redirects visitors to your <code class="doc-inline-code">eventschedule.com</code> URL. Use this if your domain's DNS is managed by Cloudflare. Cloudflare's free plan is sufficient.
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>In your schedule settings, enter your domain and select <strong class="text-gray-900 dark:text-white">Redirect</strong>.</li>
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
        <p class="text-gray-600 dark:text-gray-300 mb-4">Choose which email notifications you want for this schedule. They go to the address on the <a href="#contact-info" class="doc-link">Contact Info</a> tab, whether or not you publish it. All five are off until you turn them on.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">One setting here points the other way. <strong class="text-gray-900 dark:text-white">Email subscribers about new events</strong> decides whether your <a href="{{ route('marketing.docs.newsletters') }}#email-subscribers" class="doc-link">email subscribers</a> get an automatic digest when you publish, at most one every few days. Unlike the notifications above it is on by default, because the people receiving it asked for it when they signed up.</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Notify New Request</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive an email when someone submits a new event request to your schedule.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Notify New Fan Content</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive an email when someone submits fan content (photos or videos) to one of your events.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Notify New Sale</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive an email when a ticket sale is completed for one of your events.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Notify New Feedback</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive an email when an attendee submits post-event feedback for one of your events.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Notify New Poll Option</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive an email when a visitor suggests a new option on one of your event polls (requires <a href="{{ route('marketing.docs.creating_events') }}#polls" class="doc-link">Allow User Options</a> to be enabled).</p>
            </div>
        </div>
        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Three of them need your own email settings</div>
            <p>On the hosted platform, <strong class="text-gray-900 dark:text-white">Notify New Sale</strong>, <strong class="text-gray-900 dark:text-white">Notify New Feedback</strong> and <strong class="text-gray-900 dark:text-white">Notify New Poll Option</strong> stay greyed out until you configure <a href="#integrations-email" class="doc-link">Email Settings</a>, and a note on the tab links you straight there. New request and new fan content notifications work either way. Selfhosted installs send everything through the server's own mail configuration, so nothing is gated.</p>
        </div>

        <h4 class="font-semibold text-gray-900 dark:text-white mt-6 mb-2">Push notifications <x-doc-badge plan="pro" /></h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The same notifications can also reach you as browser and mobile push, on top of email rather than instead of it. Choose <strong class="text-gray-900 dark:text-white">Enable push on this device</strong>, allow notifications when your browser asks, then use <strong class="text-gray-900 dark:text-white">Send test push</strong> to confirm it works. Push is per device, so repeat it on your phone and your laptop. Enabling it sends notification data to OneSignal, a third-party service.</p>
        <div class="doc-callout mb-6">
            <div class="doc-callout-title">Two things have to be true</div>
            <p>The panel only appears once the operator of your Event Schedule site has configured push, which is <a href="{{ route('marketing.docs.selfhost.installation') }}#push-notifications" class="doc-link">off by default</a>. On iPhone and iPad, web push only works for sites added to the home screen (iOS 16.4 and later); Android and desktop browsers need no such step.</p>
        </div>

        <!-- Advanced Tab -->
        <h3 id="settings-advanced" class="doc-subheading">Advanced</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The Advanced tab collects the settings that change how your schedule behaves rather than how it looks. They appear in this order.</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Default new-event visibility</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The visibility every new event starts with: <strong class="text-gray-900 dark:text-white">Public</strong> or <strong class="text-gray-900 dark:text-white">Draft</strong>, plus <strong class="text-gray-900 dark:text-white">Internal</strong> and <strong class="text-gray-900 dark:text-white">Unlisted</strong> on Enterprise. Public unless you change it, and you can still set the visibility on any individual event. See <a href="{{ route('marketing.docs.creating_events') }}#draft" class="doc-link">event visibility</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Hide Past Events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Keep past events off your public schedule so visitors only ever see what is still to come. Your own admin views are unaffected, so nothing is lost - the events are still there when you need them.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Do not show other schedules' promotions</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Some Event Schedule sites run a promotions network, where schedules pay to have an event featured on other schedules' public pages. Turn this on and your pages carry nothing of the sort: no other schedule's promotions, and no ads either. It is free on every plan, and it does not stop you buying promotions of your own. See <a href="{{ route('marketing.docs.boost') }}#on-network" class="doc-link">on-network promotions</a> and <a href="{{ route('marketing.docs.managing_schedules') }}#plan" class="doc-link">ads on free schedules</a>. The toggle only appears on sites that have this switched on.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">List this schedule on the network</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Share this schedule's public events with the listings on eventschedule.com, where each listing links back to the event on your own site. Three choices: leave it undecided, list the schedule, or keep it hidden. The setting only appears once an administrator has enabled federation for the whole installation, so you will not see it on eventschedule.com itself. See <a href="{{ route('marketing.docs.selfhost.federation') }}" class="doc-link">Federation</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Hide Videos</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Hide the videos panel from your public schedule. Offered on <strong class="text-gray-900 dark:text-white">Venue</strong> and <strong class="text-gray-900 dark:text-white">Curator</strong> schedules only, because a Talent schedule's videos are part of the point.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Show Accessibility Widget</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Add an accessibility panel to your public schedule so visitors can adjust font size, contrast, and motion for themselves. Useful if you are publishing on behalf of an organization with an accessibility commitment to meet.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">First Day of Week</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Which day your calendar week starts on. All seven days are available; Sunday unless you change it.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Default Category</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Preselect one of your <a href="#customize-categories" class="doc-link">categories</a> on every new event so you do not have to pick one each time. Once saved, a button appears to apply the default to all existing events in one click. If you later remove the category, the setting is flagged so you can pick another.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Default Curator Schedules</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">If you also run Curator schedules, tick the ones new events should be shared to automatically, instead of choosing them on every event. Shown on Talent and Venue schedules that have at least one Curator schedule to offer.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Import Form Fields</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Decides which optional fields appear on the AI import <a href="#engagement-requests" class="doc-link">request form</a>: short description, description, price, coupon code, registration URL, category, and sub-schedule if you have any. The coupon code field brings its discount along with it. Turn a field on and a <strong class="text-gray-900 dark:text-white">Required</strong> checkbox appears next to it, so you can insist on an answer. Shown on the hosted platform only.</p>
            </div>
        </div>
    </section>

    <!-- Engagement -->
    <section id="engagement" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
            Engagement
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Everything to do with what visitors can send you, on six tabs: Requests, Fan Content, Feedback, Carpool, Sponsors, and Accommodation. The last one only appears on sites whose operator has enabled it.</p>

        <x-doc-screenshot id="creating-schedules--section-engagement" alt="Schedule engagement settings" />

        <!-- Requests Tab -->
        <h3 id="engagement-requests" class="doc-subheading">Requests</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Let other people put events on your schedule. A <strong class="text-gray-900 dark:text-white">Talent</strong> schedule gets a shorter version of this tab, with only the first and last settings below, because a request to book a performer is always read by hand.</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Accept Event Requests</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Turn on the public request form. What lands there is reviewed in the <a href="{{ route('marketing.docs.creating_events') }}#manual" class="doc-link">pending queue</a>. Typical uses:</p>
                <ul class="text-sm text-gray-500 dark:text-gray-400 list-disc list-inside space-y-1">
                    <li><strong class="text-gray-900 dark:text-white">Talent:</strong> let promoters ask to book you</li>
                    <li><strong class="text-gray-900 dark:text-white">Venue:</strong> take booking requests from bands and performers</li>
                    <li><strong class="text-gray-900 dark:text-white">Curator:</strong> let the community submit local events</li>
                </ul>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Require Account</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Make submitters sign in first, so every request has a name behind it. On by default for Curator schedules, off for Venue schedules. With the AI Import form, a first-time submitter completes everything on one page - their account, their own schedule, and the event - with their email confirmed by a code. Not offered on Talent schedules.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Event Request Form</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Which form visitors see: <strong class="text-gray-900 dark:text-white">AI Import</strong>, where they paste the event text or upload a flyer and the details are read out of it, or <strong class="text-gray-900 dark:text-white">Booking Form</strong>, a plain form with set fields. AI Import unless you change it. Offered on Venue and Curator schedules.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Require Approval</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">On by default. Submitted events wait in the <a href="{{ route('marketing.docs.creating_events') }}#manual" class="doc-link">pending queue</a> until you accept them; turn it off and they go straight onto your public schedule. Review them under <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Requests</strong>. Not offered on Talent schedules.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Approved Schedules</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Name schedules you already trust and their submissions skip the queue, while everyone else still waits for approval. Start typing to search and pick a schedule. Not offered on Talent schedules.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Request Terms</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Terms or guidelines a submitter has to agree to before sending a request. Use it for booking policy, technical requirements, or what you will and will not take.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Your own questions <x-doc-badge plan="pro" /></h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Add questions of your own to whichever form you use, with <a href="#customize-custom-fields" class="doc-link">Custom Fields</a> marked <strong class="text-gray-900 dark:text-white">On request form</strong>. Ask whatever you need before accepting an event: which of your equipment the visitor wants (a multiselect checklist), a reference number in a set format (a validation pattern), an expected head count. Answers appear on the request in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Requests</strong> and on the event once you accept it. A link at the bottom of this tab jumps straight to the Custom Fields tab.</p>
            </div>
        </div>

        <!-- Fan Content Tab -->
        <h3 id="engagement-fan-content" class="doc-subheading">Fan Content</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Let people who were there add to the event afterwards. Each kind has its own switch, all three are off until you turn them on, and nothing a visitor sends appears in public until you approve it.</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Fan Comments</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Written comments on your events.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Fan Photos</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Uploaded photos. A Free schedule holds up to 25 photos in total; Pro removes the cap and adds a bulk download of every photo on an event.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Fan Videos</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Links to YouTube or Vimeo videos.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Require an Account</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Off by default: attendees submit with just a name and email, so they never have to create an account. Turn it on to make them sign in first. Either way nothing appears publicly until you approve it, and the submitter's email is only ever visible to you.</p>
            </div>
        </div>

        <!-- Feedback Tab -->
        <h3 id="engagement-feedback" class="doc-subheading">Feedback <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Ask attendees what they thought once the event is over. Free schedules see the tab with the settings greyed out.</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Enable Feedback</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Once the event has ended, email everyone holding a ticket or registration for it, asking for a star rating and a comment. The rest of the tab appears once this is on.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Feedback Delay</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">How long to wait after the event ends: 1, 2, 6, 12, 24 or 48 hours. 24 hours unless you change it.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Show Feedback Publicly</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Publish the ratings and comments you collect on the event page, so people deciding whether to come can see what previous attendees said. Off by default: with it off, feedback is yours alone to read.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Send test feedback</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A button that sends you the request email as an attendee would receive it, so you can check the wording and the delivery before an event ends.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Email Settings Required</div>
            <p>On the hosted platform, feedback emails need <a href="#integrations-email" class="doc-link">Email Settings</a> configured; the settings here stay greyed out until they are.</p>
        </div>

        <h3 id="engagement-carpool" class="doc-subheading">Carpool <x-doc-badge plan="pro" /></h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Enable Carpool</h4>
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
                <p class="text-sm text-gray-500 dark:text-gray-400">A logo is required; the name, a link and a tier of <strong class="text-gray-900 dark:text-white">Gold</strong>, <strong class="text-gray-900 dark:text-white">Silver</strong> or <strong class="text-gray-900 dark:text-white">Bronze</strong> are optional. Up to {{ config('app.max_sponsors') }} sponsors per schedule.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Reordering</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Drag the handle on a sponsor to change the order they appear in on the public page.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Background</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Choose how the sponsors band blends into your page: the default panel, transparent so your own background shows through, or a color of your choosing. Text colors adjust automatically for readability.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-tip mt-4">
            <div class="doc-callout-title">Tip</div>
            <p>You can also override sponsors for individual events. See <a href="{{ route('marketing.docs.creating_events') }}#sponsors" class="doc-link">Per-Event Sponsors</a>.</p>
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
            <p>This section only appears if the operator of your Event Schedule instance has enabled the integration. It is available on <strong class="text-gray-900 dark:text-white">all plans</strong>, including Free.</p>
        </div>
    </section>

    <!-- Auto Import -->
    <!-- Event Sources -->
    <section id="event-sources" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
            </svg>
            Event Sources <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 ml-2">Curator</span>
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">List the talent and venue schedules you want to follow and everything they publish shows up on your calendar on its own. It covers what they have already run as well as what is coming, and each new event appears within a few minutes of going live. This is the fastest way to stand up a city guide or a festival hub: pick your rooms and your acts once, and stop copying listings by hand.</p>

        <x-doc-screenshot id="creating-schedules--section-sources" alt="Event sources settings" />

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Schedules</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Search by name or address and pick the talent or venue schedule you want. Only talent and venue schedules can be a source, so one curator never chains onto another.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Anything the schedule has chosen not to publish stays private: drafts, internal events, unlisted events and anything it has not accepted are all left out.</p>
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
            <li>Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Edit Schedule</strong> on a curator schedule and choose <strong class="text-gray-900 dark:text-white">Event Sources</strong>.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">+ Add Schedule</strong> and search for the talent or venue you want to follow.</li>
            <li>Optionally choose a sub-schedule to file that source's events under.</li>
            <li>Save. Their events, past and upcoming, appear on your calendar right away.</li>
        </ol>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Sourced events count as fully yours: they show on your calendar and public page, in your <a href="/docs/event-graphics" class="doc-link">event graphics</a>, and in your iCal and RSS feeds. To drop a single one, open it and choose Remove from schedule; it stays gone even though the source is still connected. Removing the source itself takes its events with it and leaves anything you added by hand untouched.</p>
        </div>
    </section>

    <section id="auto-import" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
            Auto Import <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 ml-2">Selfhost</span>
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Point Event Schedule at a page that lists events and it reads them once a day, so a venue calendar or a tour page keeps your schedule current without you retyping anything. This section only exists on selfhosted installs, and it needs an AI key configured on the server.</p>

        <x-doc-screenshot id="creating-schedules--section-auto-import" alt="Auto import settings" />

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
            <li>Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Edit Schedule</strong> and choose <strong class="text-gray-900 dark:text-white">Auto Import</strong>.</li>
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
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z" />
            </svg>
            Integrations
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Connect your schedule to the outside world: send its email through your own server, keep it in step with a calendar you already use, and hand out feed URLs other apps can subscribe to. The tabs are <strong class="text-gray-900 dark:text-white">Email Settings</strong> (hosted platform only), <strong class="text-gray-900 dark:text-white">Google Calendar</strong>, <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong>, <strong class="text-gray-900 dark:text-white">CalDAV Calendar</strong>, and <strong class="text-gray-900 dark:text-white">Advanced</strong>.</p>

        <x-doc-screenshot id="creating-schedules--section-integrations" alt="Calendar integration settings" />

        <p class="text-gray-600 dark:text-gray-300 mb-4">All three calendar integrations are <a href="{{ route('marketing.pricing') }}" class="doc-link">free on every plan</a> and work the same way: choose a calendar, choose a direction, save.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Sync direction</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Four choices per integration: push your events out to the calendar, pull that calendar's events in, keep both in step, or <strong class="text-gray-900 dark:text-white">No sync</strong>, which is where every integration starts.</p>
            </div>
            <div id="delete-sync" class="scroll-mt-24 bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">When an event is deleted in the connected calendar</h4>
                <p class="text-sm text-gray-600 dark:text-gray-300">Shown on the Google and Outlook tabs once that integration is pulling events in. Choose what happens here when you delete an event there: <strong class="text-gray-900 dark:text-white">Keep it here</strong> (the default), <strong class="text-gray-900 dark:text-white">Mark as cancelled</strong> (hidden but reversible), or <strong class="text-gray-900 dark:text-white">Delete it here</strong>. Deleting is permanent, so an event with ticket sales or a running ad boost is hidden rather than deleted.</p>
            </div>
        </div>

        <div class="doc-callout mb-6">
            <div class="doc-callout-title">A recurring event syncs as one entry</div>
            <p>Event Schedule does not send a repeat rule to a connected calendar, so a weekly event arrives there as a single entry on its start date. If you want every date to show up in someone's calendar app, give them the <a href="#integrations-advanced" class="doc-link">iCal feed</a> instead, which lists each occurrence.</p>
        </div>

        <!-- Email -->
        <h3 id="integrations-email" class="doc-subheading">Email Settings</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Send this schedule's mail - ticket confirmations, notifications, feedback requests and <a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">newsletters</a> - through your own SMTP server and from your own address.</p>

        <x-doc-screenshot id="creating-schedules--section-email-settings" alt="Email settings" />

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Availability</div>
            <p>Per-schedule email settings are a hosted-platform feature, available on every plan. Selfhosted installs configure mail once at the server level instead, so the tab is not shown - see the <a href="{{ route('marketing.docs.selfhost.email') }}" class="doc-link">selfhost email docs</a>.</p>
        </div>

        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4">Setting up custom email</h4>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Edit Schedule</strong>, choose <strong class="text-gray-900 dark:text-white">Integrations</strong>, and stay on the <strong class="text-gray-900 dark:text-white">Email Settings</strong> tab.</li>
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
            <p>If your SMTP credentials start failing, an amber dot appears beside the <strong class="text-gray-900 dark:text-white">Email Settings</strong> tab and a warning banner inside it, with a <strong class="text-gray-900 dark:text-white">Show error details</strong> link carrying the provider's own message. Delivery is paused while settings are failing; Event Schedule retries after 24 hours, or immediately once a test email succeeds. Fix the underlying problem, then send a test email to resume delivery right away.</p>
        </div>

        <!-- Google Calendar -->
        <h3 id="integrations-google" class="doc-subheading">Google Calendar</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Keep your schedule and a Google Calendar in step. Google tells Event Schedule about changes as they happen, so an edit made on either side shows up on the other without waiting for a poll.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Connect your Google account first, in <a href="{{ route('marketing.docs.account_settings') }}#google" class="doc-link">Account Settings</a>. Then:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Edit Schedule</strong> and choose <strong class="text-gray-900 dark:text-white">Integrations &rarr; Google Calendar</strong>.</li>
            <li>Pick the <strong class="text-gray-900 dark:text-white">calendar</strong> to sync with from the dropdown.</li>
            <li>Choose a <strong class="text-gray-900 dark:text-white">sync direction</strong>: to Google Calendar, from Google Calendar, bidirectional, or no sync.</li>
            <li>If you are pulling events in, set <a href="#delete-sync" class="doc-link">what happens when an event is deleted there</a>.</li>
            <li>Save. The schedule's owner also gets a <strong class="text-gray-900 dark:text-white">Sync events</strong> button for forcing a full re-sync if the two ever drift apart.</li>
        </ol>

        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">Team members</div>
            <p>Under <strong class="text-gray-900 dark:text-white">Sync to my calendar</strong>, each team member can point the schedule's events at a calendar of their own. That choice is theirs alone and is separate from the schedule-wide sync above, so everyone can follow the schedule in their own Google account.</p>
        </div>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Selfhost note</div>
            <p>Google Calendar sync needs Google API credentials on the server. See the <a href="{{ route('marketing.docs.selfhost.google_calendar') }}" class="doc-link">selfhost Google Calendar docs</a> for setup instructions.</p>
        </div>

        <!-- Outlook / Microsoft Calendar -->
        <h3 id="integrations-microsoft" class="doc-subheading">Outlook Calendar</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The same two-way sync against an Outlook or Microsoft 365 calendar, over the Microsoft Graph API. Changes arrive near-instantly, with regular polling as a safety net.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Connect your Outlook account first, in <a href="{{ route('marketing.docs.account_settings') }}#microsoft" class="doc-link">Account Settings</a>. Then:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Edit Schedule</strong> and choose <strong class="text-gray-900 dark:text-white">Integrations &rarr; Outlook Calendar</strong>.</li>
            <li>Pick the <strong class="text-gray-900 dark:text-white">calendar</strong> to sync with.</li>
            <li>Choose a <strong class="text-gray-900 dark:text-white">sync direction</strong>: to Outlook, from Outlook, bidirectional, or no sync.</li>
            <li>If you are pulling events in, set <a href="#delete-sync" class="doc-link">what happens when an event is deleted there</a>.</li>
            <li>Optionally turn on <strong class="text-gray-900 dark:text-white">Teams meetings</strong> so an online event with no venue is created as a Microsoft Teams meeting and its join link is saved back onto the event.</li>
        </ol>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Selfhost note</div>
            <p>Outlook Calendar sync needs an Azure app registration. See the <a href="{{ route('marketing.docs.selfhost.microsoft_calendar') }}" class="doc-link">selfhost Outlook Calendar docs</a> for setup instructions.</p>
        </div>

        <!-- CalDAV -->
        <h3 id="integrations-caldav" class="doc-subheading">CalDAV Calendar</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">CalDAV is the open standard behind Apple Calendar, Fastmail, Nextcloud and many others, so this tab covers everything the two above do not. There is no webhook in the standard, so changes are picked up on a regular sweep rather than the moment they happen.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Edit Schedule</strong> and choose <strong class="text-gray-900 dark:text-white">Integrations &rarr; CalDAV Calendar</strong>.</li>
            <li>Enter the <strong class="text-gray-900 dark:text-white">server URL</strong>, <strong class="text-gray-900 dark:text-white">username</strong> and <strong class="text-gray-900 dark:text-white">password</strong>. Providers that use two-factor authentication usually want an app-specific password here rather than your account password.</li>
            <li>Pick the <strong class="text-gray-900 dark:text-white">calendar</strong> to sync with.</li>
            <li>Choose a <strong class="text-gray-900 dark:text-white">sync direction</strong>: to CalDAV, from CalDAV, or bidirectional.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Connect</strong>.</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Once connected, the tab shows which server you are attached to, and you can disconnect at any time to stop syncing.
        </p>

        <!-- Advanced -->
        <h3 id="integrations-advanced" class="doc-subheading">Advanced</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The Advanced tab holds two things: the wording used for your events inside a connected calendar, and the read-only feed URLs for your schedule.</p>

        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Calendar Description Template</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            An event pushed out to Google Calendar, Outlook or CalDAV normally arrives carrying its own description and nothing else. Set a <strong class="text-gray-900 dark:text-white">Calendar Description Template</strong> and every outbound event uses your wording instead, built from the same variables as <a href="{{ route('marketing.docs.event_graphics') }}#variables" class="doc-link">event graphics</a>. Leave it empty and the event description is used as-is.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            A line whose variables all come out empty is dropped rather than left as a stray separator, so one template can serve events with a venue and events without.
        </p>

        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">Example</div>
            <p>A template like:</p>
            <pre class="bg-gray-100 dark:bg-gray-800 rounded p-3 text-sm mt-2 mb-2"><code>{event_name}
{date_full_dmy} {time}
{venue} | {city}

{description}

{url}</code></pre>
            <p>Would produce a calendar description like:</p>
            <pre class="bg-gray-100 dark:bg-gray-800 rounded p-3 text-sm mt-2"><code>Summer Concert
15/03/2025 20:00
Central Park | New York

Join us for a night of music...

example.eventschedule.com/summer-concert</code></pre>
        </div>

        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3" id="available-variables">Available Variables</h4>
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
                                <td><code class="doc-inline-code">{custom_{{ $loop->iteration }}}</code></td>
                                <td>{{ $field['name'] }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $field['type'] ?? 'string')) }}</td>
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

        <hr class="border-gray-200 dark:border-gray-700 my-8">

        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Feeds</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The bottom of the Advanced tab gives you two read-only addresses, each with a copy button, that let anyone follow your schedule from an app of their own. They need no login and they update themselves as you add or change events.</p>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">iCal Feed</strong> - subscribe from any calendar app (Google Calendar, Apple Calendar, Outlook). Unlike a connected calendar, this feed lists a recurring event on every date it falls in the next 90 days, not just the first.</li>
            <li><strong class="text-gray-900 dark:text-white">RSS Feed</strong> - follow your schedule from any RSS reader.</li>
        </ul>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Note</div>
            <p>Feed URLs only exist once the schedule has been saved. Create and save your schedule first, then come back to the Advanced tab to find them.</p>
        </div>
    </section>

    <!-- See Also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
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
                    "text": "Open Admin Panel, then Schedule, then Edit Schedule, and set your schedule name, short description, and description, which supports Markdown formatting.",
                    "url": "{{ url(route('marketing.docs.creating_schedules')) }}#details"
                },
                {
                    "@type": "HowToStep",
                    "name": "Set Your Address",
                    "text": "For Venue schedules, add your full address including street, city, state, postal code, and country for map integration.",
                    "url": "{{ url(route('marketing.docs.creating_schedules')) }}#address"
                },
                {
                    "@type": "HowToStep",
                    "name": "Configure Settings",
                    "text": "Set your schedule URL, language, timezone, time format, and configure event request and approval settings.",
                    "url": "{{ url(route('marketing.docs.creating_schedules')) }}#settings"
                },
                {
                    "@type": "HowToStep",
                    "name": "Set Up Auto Import",
                    "text": "Add URLs or city names to automatically import events from external sources.",
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
