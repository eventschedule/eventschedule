<x-docs-page
    key="ai-import"
    title="AI Import Guide: Links, Text and Flyers - Event Schedule"
    description="Paste a link, event text or a flyer image and each event's name, date, venue, price and performers are filled in for you to review before adding."
    lede="Save hours of manual data entry. Paste a link to your events, paste event text or add a flyer image, and the details are filled in for you."
>
    <x-slot:toc>
        <x-doc-nav-group label="AI Import" href="#ai-import" expanded>
            <x-doc-nav-link href="#link-import">From a Link</x-doc-nav-link>
            <x-doc-nav-link href="#google-import">From Google Calendar</x-doc-nav-link>
            <x-doc-nav-link href="#text-import">From Text</x-doc-nav-link>
            <x-doc-nav-link href="#new-pages">New Pages and Requests</x-doc-nav-link>
            <x-doc-nav-link href="#image-import">From Images/Flyers</x-doc-nav-link>
            <x-doc-nav-link href="#choosing-events">Choosing What to Add</x-doc-nav-link>
            <x-doc-nav-link href="#undo-import">Undoing an Import</x-doc-nav-link>
            <x-doc-nav-link href="#custom-prompts">Custom AI Prompts</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- AI Import -->
    <section id="ai-import" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
            </svg>
            Let AI Do the Heavy Lifting
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The import page takes event information from wherever it already lives and turns it into events you review before adding. It accepts three kinds of input: a <strong class="text-gray-900 dark:text-white">link</strong> to an events page or a calendar, <strong class="text-gray-900 dark:text-white">text</strong> you type or paste, and an <strong class="text-gray-900 dark:text-white">image</strong> such as a flyer or poster. Each event comes back with its name, date and time, venue, description, price and more. One event comes back as an editable card; two or more come back as a <a href="#choosing-events" class="doc-link">list you choose from</a>. Nothing is added to your schedule until you add it.</p>

        <x-doc-screenshot id="creating-events--import" alt="Import events page" loading="eager" />

        <h3 class="doc-subheading">Opening the Import Page</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open your schedule in the admin panel</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Actions</strong> in the top right</li>
            <li>Choose <strong class="text-gray-900 dark:text-white">Import Events</strong></li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Under the box, <strong class="text-gray-900 dark:text-white">Other ways to bring events in</strong> holds <a href="#google-import" class="doc-link">Google Calendar</a>, for the schedule's owner, and <strong class="text-gray-900 dark:text-white">Import from Eventbrite</strong> <x-doc-badge plan="pro" />, the connector that pulls your events, tickets and venues from a connected Eventbrite account.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">A link is a one-time copy</div>
            <p>Reading a link copies what is listed there now. Events added to that page or calendar later do not appear on your schedule by themselves: read the link again and the ones you already have are left out, or come back unticked. To keep a calendar in step continuously, connect it under <a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="doc-link">Integrations</a> instead.</p>
        </div>

        <div class="doc-callout doc-callout-tip mt-4">
            <div class="doc-callout-title">AI-Powered</div>
            <p>Text, images and pages without event data are read by Google Gemini or by OpenAI, whichever is configured. A calendar link, or a page that publishes its events as data, is read directly and involves no AI. On the hosted service everything is ready to use. On a selfhosted install with neither <code class="doc-inline-code">GEMINI_API_KEY</code> nor <code class="doc-inline-code">OPENAI_API_KEY</code> set, the box takes links only, and an installation admin sees a <strong class="text-gray-900 dark:text-white">Get API Key</strong> panel marked <em>Optional</em>, see <a href="{{ route('marketing.docs.selfhost.ai') }}" class="doc-link">AI Setup</a>.</p>
        </div>

        <h3 class="doc-subheading">What You Can Send</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Link</strong> - One web address on its own: an events page, or a calendar. Up to 100 events come back per read</li>
            <li><strong class="text-gray-900 dark:text-white">Text</strong> - Up to 10,000 characters per submission</li>
            <li><strong class="text-gray-900 dark:text-white">Image</strong> - One JPG, PNG, GIF or WebP file of up to 10 MB per submission. A large photo is shrunk in your browser before it is sent, so a picture straight off a phone camera is fine</li>
            <li><strong class="text-gray-900 dark:text-white">Both together</strong> - A flyer plus a line of text covering whatever the flyer leaves out</li>
        </ul>

        <h3 class="doc-subheading">Daily Limits</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Each text or image submission counts as one AI request, whether it is text, an image or both, and one request can return several events. A link counts only when the AI has to read the page: a calendar feed, or a page that publishes its events as data, is read directly and uses none of the allowance. The allowance is counted per schedule per day on the hosted service. When it runs out the page asks you to try again tomorrow.</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>AI import requests per day</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>During a paid-plan trial</td>
                        <td>10</td>
                    </tr>
                    <tr>
                        <td>Free or Pro</td>
                        <td>50</td>
                    </tr>
                    <tr>
                        <td>Enterprise</td>
                        <td>100</td>
                    </tr>
                    <tr>
                        <td>Selfhosted</td>
                        <td>No limit</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The trial row is checked first, so a schedule inside a paid-plan trial gets the trial allowance rather than its plan's. Separately from the daily count there are two short-term ceilings: 30 submissions a minute, and 10 links a minute. Meeting either shows a message asking you to wait a minute, and it clears on its own.</p>
        <p class="text-gray-600 dark:text-gray-300">A venue or curator schedule that accepts event requests without requiring visitors to have an account shows those visitors this same AI box instead of a structured form. Their box takes text and images, not links. Their submissions count against the schedule's daily allowance too. See <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-requests" class="doc-link">Requests</a> for the submission form options.</p>
    </section>

    <!-- Link Import -->
    <section id="link-import" class="doc-section">
        <h3 class="doc-subheading">Importing from a Link</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Paste the address of the page or calendar where your events are already listed. When the box holds a link and nothing else, a line under it reads <em>We will read this page</em> and the button reads <strong class="text-gray-900 dark:text-white">Read link</strong>.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Paste the link into the box. The <code class="doc-inline-code">https://</code> is optional, so <code class="doc-inline-code">yourvenue.com/events</code> works</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Read link</strong>, or press <strong class="text-gray-900 dark:text-white">Ctrl+Enter</strong>. The page names the site it is reading, and <strong class="text-gray-900 dark:text-white">Cancel</strong> stops it</li>
            <li>Review what came back: one event as a card, two or more as a <a href="#choosing-events" class="doc-link">list</a></li>
            <li>Click <strong class="text-gray-900 dark:text-white">Save</strong> on a single card, or the button under a list, which carries the number you ticked, for example <strong class="text-gray-900 dark:text-white">Add 12 events</strong></li>
        </ol>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>What the link points to</th>
                        <th>How it is read</th>
                        <th>Uses AI</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>A calendar feed: an <code class="doc-inline-code">.ics</code> or <code class="doc-inline-code">webcal://</code> address</td>
                        <td>As written, for the next 12 months</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>A public Google Calendar link, or a published Outlook calendar page</td>
                        <td>Swapped for that calendar's feed address, then read as a feed</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>A page that publishes its events as data, the structured event data many website builders and ticketing sites add for search engines</td>
                        <td>From that data, for the next 12 months</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>Any other web page</td>
                        <td>The page's main text, up to 10,000 characters, is read the way pasted text is</td>
                        <td>Yes, one request</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Repeating events</strong> - A repeating calendar entry arrives as one repeating event when your schedule can express its rule: daily, weekly on chosen days, every few weeks, monthly or yearly, with an end date or a number of occurrences. A rule it cannot express, such as the last Friday of every month, arrives as one row holding its next 12 dates. So does a series with a moved date, and one whose clock time would drift on your schedule because it is set in another time zone</li>
            <li><strong class="text-gray-900 dark:text-white">Time zone</strong> - A line above the results reads <em>Times shown in</em> followed by your schedule's time zone, with a link to change it. On a talent or curator schedule, an event that states its own time zone keeps its local clock time, so an 8 PM show in another city stays 8 PM, and its row says so</li>
            <li><strong class="text-gray-900 dark:text-white">All-day entries</strong> - Shown as <em>All day</em>, and saved starting at midnight for the full day</li>
            <li><strong class="text-gray-900 dark:text-white">Left out</strong> - From a calendar or a page's event data: cancelled entries, entries marked private, past events, and events your schedule already has, including dates a repeating event of the same name already covers. The results say how many were left out because you already have them, and how many entries could not be read at all. From a page the AI read, a likely duplicate comes back unticked instead</li>
            <li><strong class="text-gray-900 dark:text-white">More than 100</strong> - A read returns up to 100 events and says how many more there are. Add those, then read the link again for the rest</li>
            <li><strong class="text-gray-900 dark:text-white">Missing some?</strong> - When a page's event data does not cover everything on the page, <strong class="text-gray-900 dark:text-white">Missing some? Read the whole page</strong> reads the page's text with AI instead</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">What a link cannot reach</div>
            <p>Facebook and Instagram do not let other sites read their pages, so those links are turned down straight away: add a screenshot of your events instead. A Google Calendar that is not public cannot be read from its link. A page behind a sign-in, or one that only fills in its events after it loads, comes back with no events: paste its text or add a screenshot. Links are read on this import page only, not on the public request form.</p>
        </div>
    </section>

    <!-- Google Calendar Import -->
    <section id="google-import" class="doc-section">
        <h3 class="doc-subheading">Importing from Google Calendar</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">When your events live in a Google calendar that is not public, connect it and choose which of its events to bring over. This is for the schedule's owner, and it copies: nothing in your Google calendar is changed.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>On the import page, under <strong class="text-gray-900 dark:text-white">Other ways to bring events in</strong>, click <strong class="text-gray-900 dark:text-white">Google Calendar</strong></li>
            <li>Click <strong class="text-gray-900 dark:text-white">Connect Google Calendar</strong>. Google asks whether Event Schedule may <em>see</em> your calendars, and nothing more: the page says <em>Read-only. We never change your calendar.</em> and that is what is requested</li>
            <li>Back on the import page, pick one calendar under <strong class="text-gray-900 dark:text-white">Choose a calendar</strong>. Your own calendars come first and the account's <strong class="text-gray-900 dark:text-white">Main calendar</strong> last. Google's holiday, birthday and week-number calendars are not offered</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Show events</strong>, then tick what you want in the <a href="#choosing-events" class="doc-link">list</a> and add it</li>
        </ol>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">What comes over</strong> - The calendar's events for the next 12 months, up to 100 at a time, read the way a <a href="#link-import" class="doc-link">calendar link</a> is: a repeating event as one repeating event, an all-day entry as all day, times in your schedule's time zone</li>
            <li><strong class="text-gray-900 dark:text-white">What is left out</strong> - Entries marked private, cancelled entries, events already on your schedule, and things that are not events: birthdays, working locations, out-of-office and focus-time blocks, and entries Google made from your mail</li>
            <li><strong class="text-gray-900 dark:text-white">Use a different account</strong> - Offered beside the connected address while none of your schedules is synced with Google Calendar, since a sync runs on the same connection</li>
            <li><strong class="text-gray-900 dark:text-white">A one-time copy</strong> - Events added to that calendar later do not arrive by themselves: choose the calendar again to add them, or turn on a sync under <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-google" class="doc-link">Integrations</a></li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Read-only until you say otherwise</div>
            <p>A connection made here can read your calendars and cannot write to them. That is enough to import, and to sync <em>from</em> Google Calendar. To send your schedule's events <em>to</em> Google Calendar, open <strong class="text-gray-900 dark:text-white">Edit Schedule &rarr; Integrations &rarr; Google Calendar</strong> and click <strong class="text-gray-900 dark:text-white">Allow at Google</strong>. On a selfhosted install the Google Calendar source appears once Google API credentials are set, see the <a href="{{ route('marketing.docs.selfhost.google_calendar') }}" class="doc-link">selfhost Google Calendar docs</a>.</p>
        </div>
    </section>

    <!-- Text Import -->
    <section id="text-import" class="doc-section">
        <h3 class="doc-subheading">Importing from Text</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Copy event information from an email, a website listing, a social media post or a message, and paste it into the import box.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Type or paste the event text into the box, which reads <em>Paste a link, paste text, or add a flyer</em> while it is empty</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Read text</strong> at the end of the box, or press <strong class="text-gray-900 dark:text-white">Ctrl+Enter</strong>. Enter on its own starts a new line</li>
            <li>Review what the AI found and correct anything it got wrong. One event comes back as a card; two or more come back as a <a href="#choosing-events" class="doc-link">list</a></li>
            <li>Click <strong class="text-gray-900 dark:text-white">Save</strong> on the card to create the event</li>
            <li>A saved card turns green and offers <strong class="text-gray-900 dark:text-white">Edit</strong>, <strong class="text-gray-900 dark:text-white">View</strong> and <strong class="text-gray-900 dark:text-white">Clear</strong> so you can move straight on to the next import</li>
        </ol>

        <h4 class="font-semibold text-gray-900 dark:text-white mb-2 mt-6">Example</h4>
        <div class="doc-code-block doc-code-block--wrap">
            <div class="doc-code-header">
                <span>Pasted text</span>
            </div>
            <pre><code>Live Jazz Night
Friday, March 15th at 8pm
The Blue Note, 123 Main Street
Featuring the John Smith Trio
Tickets: $20</code></pre>
        </div>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Notes</th>
                        <th>From the example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Event name</td>
                        <td>A short version is also generated for the event URL</td>
                        <td>Live Jazz Night</td>
                    </tr>
                    <tr>
                        <td>Date and start time</td>
                        <td>When no time is given, 8:00 PM is assumed. Both are editable on the card, the date through a picker and the time through a dropdown</td>
                        <td>March 15, 8:00 PM</td>
                    </tr>
                    <tr>
                        <td>End time</td>
                        <td>Worked out from the length the AI reads out of the text, in hours, and added to the start time. Left blank when no end time or length is mentioned. The event's duration is taken from whatever start and end you save</td>
                        <td>Left blank</td>
                    </tr>
                    <tr>
                        <td>Venue and address</td>
                        <td>Venue name, street address, city, state and postal code, plus a two-letter country code. A curator schedule falls back to its own country when the text does not name one</td>
                        <td>The Blue Note, 123 Main Street</td>
                    </tr>
                    <tr>
                        <td>Description</td>
                        <td>Markdown, plus an optional one-line short description of up to 200 characters</td>
                        <td>Featuring the John Smith Trio</td>
                    </tr>
                    <tr>
                        <td>Participants</td>
                        <td>Performer name, email and website. The name is matched first against talent schedules in your schedule's country, then against talent already connected to your schedule. On a talent schedule every imported event is pinned to that talent instead</td>
                        <td>John Smith Trio</td>
                    </tr>
                    <tr>
                        <td>Price</td>
                        <td>Amount and currency code; blank means unknown and 0 means free</td>
                        <td>20 USD</td>
                    </tr>
                    <tr>
                        <td>Category</td>
                        <td>Chosen from the categories your schedule uses, including your custom ones. A near-miss is matched back to the closest name on your list</td>
                        <td>Concerts</td>
                    </tr>
                    <tr>
                        <td>Registration URL</td>
                        <td>An external ticket or sign-up link found in the text</td>
                        <td>None in this text</td>
                    </tr>
                    <tr>
                        <td>Custom fields <x-doc-badge plan="pro" /></td>
                        <td>Your own event fields are filled in too, matched against their allowed options</td>
                        <td>None defined</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Several events at once</strong> - Paste a whole list. If the text names several distinct performers the AI splits them into separate events, and events that share a start time and address are merged back into one event with several participants</li>
            <li><strong class="text-gray-900 dark:text-white">Sensible dates only</strong> - A parsed date that is more than three days in the past is left blank for you to fill in rather than guessed. This applies to whatever the AI reads; a calendar feed or a page's own event data keeps the dates it gives</li>
            <li><strong class="text-gray-900 dark:text-white">Other languages</strong> - For a schedule whose language is not English, the AI keeps the original language and adds English translations alongside it</li>
            <li><strong class="text-gray-900 dark:text-white">Which fields appear</strong> - Name, date and time, and venue are always on the card. The extra fields (short description, description, price, coupon code and its discount, registration URL, category and sub-schedule) are switched on per schedule, and can be marked required. The sub-schedule field only appears once the schedule has sub-schedules</li>
            <li><strong class="text-gray-900 dark:text-white">Performer videos</strong> - On a curator schedule, when a parsed performer does not match a talent schedule you already have, the card searches YouTube for that name and offers up to six clips. Pick one and it becomes the new talent schedule's video</li>
        </ul>

        <h4 class="font-semibold text-gray-900 dark:text-white mb-2 mt-6">Venue Matching</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Rather than creating a duplicate, the import looks for a venue you already have. It checks, in order, a venue schedule you own with the same name, then any venue in the same city (and country, when one is known) with a matching name or street address, then venues connected to your schedule through past events. Matching ignores case, accents, punctuation and a leading "the", so "The Blue Note" and "Blue Note" are the same venue. A hit shows as <strong class="text-gray-900 dark:text-white">Matched venue</strong> and the card's venue block is set to <strong class="text-gray-900 dark:text-white">Use Existing</strong>.</p>
        <ul class="doc-list mb-6">
            <li>Switch to <strong class="text-gray-900 dark:text-white">Create New</strong> to enter the name, street address and city yourself</li>
            <li>The venue dropdown groups your options into <strong class="text-gray-900 dark:text-white">Member</strong>, <strong class="text-gray-900 dark:text-white">Following</strong> and <strong class="text-gray-900 dark:text-white">From past events</strong></li>
            <li>Tick <strong class="text-gray-900 dark:text-white">I manage this venue, make me the owner</strong> when the new venue is yours to run</li>
            <li>On a venue schedule there is nothing to match: every imported event is pinned to that venue</li>
        </ul>

        <h4 class="font-semibold text-gray-900 dark:text-white mb-2 mt-6">Duplicate Warning</h4>
        <p class="text-gray-600 dark:text-gray-300">A card shows <strong class="text-gray-900 dark:text-white">Similar event found</strong> when your schedule already has an upcoming event with the same registration URL, or with the same start time plus either the same venue address or the same performer name. <strong class="text-gray-900 dark:text-white">View</strong> opens the existing event so you can decide whether to save the new one. On a curator schedule, <strong class="text-gray-900 dark:text-white">Select</strong> adds that existing event to your schedule instead of creating a second copy.</p>

        <h4 id="new-pages" class="font-semibold text-gray-900 dark:text-white mb-2 mt-6">New Pages and Requests</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Saving a card also reaches the performers and the venue it names, in one of two ways:</p>
        <ul class="doc-list">
            <li><strong class="text-gray-900 dark:text-white">Not on Event Schedule yet</strong> - A performer or venue the import did not match gets a schedule page of its own when you save, so your event page can link to them. The page is public but stays out of search engines, says which schedule created it, and offers <strong class="text-gray-900 dark:text-white">Claim this page</strong>, which works for whoever signs in with the email address on it (or its phone number, when it has no email). If that address already belongs to an account, the page is theirs straight away and the event reaches it as a request, as below. Tick <strong class="text-gray-900 dark:text-white">I manage this venue, make me the owner</strong> to make a new venue yours instead. See <a href="{{ route('marketing.docs.creating_events') }}#claim" class="doc-link">Pages Created for Others</a> for claiming and invitations</li>
            <li><strong class="text-gray-900 dark:text-white">Already running a schedule</strong> - The event is on your schedule straight away, but reaches theirs as a request they accept or decline, unless your schedule is on their approved list. A performer's schedule always works this way; a venue can choose to accept requests without approval</li>
        </ul>
    </section>

    <!-- Image Import -->
    <section id="image-import" class="doc-section">
        <h3 class="doc-subheading">Importing from Images/Flyers</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Give the AI a flyer, poster or screenshot and it reads the text out of the image.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Add the image in whichever way suits you: click <strong class="text-gray-900 dark:text-white">Add Image</strong> to pick a file, paste an image from your clipboard into the box, or drag and drop the file onto the box</li>
            <li>A thumbnail appears in the corner of the box. Use the red x on it to remove the image if you picked the wrong one</li>
            <li>Optionally type a note in the box as well, for anything the flyer leaves out</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Read flyer</strong> to submit</li>
            <li>Review the card, then click <strong class="text-gray-900 dark:text-white">Save</strong></li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The image you submitted is carried over as the event's image and shown beside the parsed fields, so a flyer import needs no separate upload. You can remove it there, or drop a different image onto that panel before saving. The one exception is text that also contains a ticket link: that link's own preview image wins, because it is usually the publisher's artwork.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Image Tips</div>
            <p>One image per submission, in JPG, PNG, GIF or WebP, up to 10 MB. A large photo is shrunk in your browser before it is sent. Use a clear, high-contrast picture where the text is large enough to read: the AI can only extract what is legible. Photograph the flyer flat and in full frame rather than at an angle.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mt-6">Photographing a printed <em>agenda</em> to create timed parts inside one existing event is a separate feature. See <a href="{{ route('marketing.docs.scan_agenda') }}" class="doc-link">Scan Agenda</a> <x-doc-badge plan="enterprise" />.</p>
    </section>

    <!-- Choosing What to Add -->
    <section id="choosing-events" class="doc-section">
        <h3 class="doc-subheading">Choosing What to Add</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">When a read finds two or more events, from a link, from text or from an image, they come back as one list rather than a card each.</p>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Rows</strong> - Each row shows the date, the name, and a line with the time, the venue and how the event repeats</li>
            <li><strong class="text-gray-900 dark:text-white">Ticking</strong> - Tick the rows you want. <strong class="text-gray-900 dark:text-white">Select all</strong> ticks every row that can be added, and the count beside it keeps track, for example <em>12 of 14 selected</em></li>
            <li><strong class="text-gray-900 dark:text-white">Details</strong> - Click a row to open its full card in place, where every field can be edited. One row is open at a time</li>
            <li><strong class="text-gray-900 dark:text-white">Rows that start unticked</strong> - A row missing a name, date or time reads <em>Add a name, date and time</em>. A row that matches an event you already have reads <em>Looks like one you already have</em>. Open the row to fix or check it, then tick it</li>
            <li><strong class="text-gray-900 dark:text-white">Adding</strong> - The button at the foot of the list carries the count, for example <strong class="text-gray-900 dark:text-white">Add 12 events</strong>. While it works a bar reads <em>Saving 3 of 12</em> and each row shows a tick as it lands. When everything is added you are taken to your schedule</li>
            <li><strong class="text-gray-900 dark:text-white">When something does not save</strong> - The page stays, says how many were added and how many need attention, and shows the reason on each row concerned. Such a row stays ticked to be tried again: untick it to leave it out</li>
            <li><strong class="text-gray-900 dark:text-white">A series listed by date</strong> - One row stands for all of its dates. Ticking, removing or saving it applies to every date, and what you change on its card (its name, its venue) is carried to each of them</li>
            <li><strong class="text-gray-900 dark:text-white">Start over</strong> - Clears the results and returns to the empty box, after asking when there are events you have not added yet</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300">On the hosted service a schedule can only create so many events in one day. An import that reaches that limit stops there and says so; the rest can be added the next day.</p>
    </section>

    <!-- Undoing an Import -->
    <section id="undo-import" class="doc-section">
        <h3 class="doc-subheading">Undoing an Import</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">An import ends on your schedule, under a panel headed <strong class="text-gray-900 dark:text-white">Events added to your schedule</strong> with the count, the first few names and your schedule's link to copy.</p>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Add to your website</strong> - Opens the embed dialog on the calendar, see <a href="{{ route('marketing.docs.sharing') }}#embed" class="doc-link">Embedding on Your Website</a></li>
            <li><strong class="text-gray-900 dark:text-white">View schedule</strong> - Opens the public page your guests see</li>
            <li><strong class="text-gray-900 dark:text-white">Import more</strong> - Returns to the import page. After an import of fewer than five events this button reads <strong class="text-gray-900 dark:text-white">Add more events</strong> and is the one highlighted</li>
            <li><strong class="text-gray-900 dark:text-white">Undo this import</strong> - Removes the events that import added, after asking you to confirm</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Undo only touches what that import added. Events you created by hand stay, earlier imports stay, and an imported event that someone already holds a ticket or a booking for is kept; the page then says how many were removed and how many were kept.</p>
        <p class="text-gray-600 dark:text-gray-300">Undo is offered for 24 hours, in the browser you imported with and while you stay signed in. If you left the import page without finishing, the next time you open it your last import is named at the top with the same <strong class="text-gray-900 dark:text-white">Undo this import</strong> button.</p>
    </section>

    <!-- Custom AI Prompts -->
    <section id="custom-prompts" class="doc-section">
        <h3 class="doc-subheading">Custom AI Prompts</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The import page itself has no prompt box: you type event details, not instructions. What the parser knows about your schedule comes from the settings below, so this is where you steer it when it keeps getting something wrong.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Where to find it</th>
                        <th>What it does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>AI prompt on a custom field <x-doc-badge plan="pro" /></td>
                        <td>Edit schedule &rarr; Customize &rarr; Custom Fields</td>
                        <td>An optional instruction, up to 500 characters, on how to extract that one field's value. It is appended to the parser's instructions for that field</td>
                    </tr>
                    <tr>
                        <td>Event categories</td>
                        <td>Edit schedule &rarr; Customize &rarr; Categories</td>
                        <td>The parser picks a category from your schedule's own list, so renaming or adding categories changes what it can choose</td>
                    </tr>
                    <tr>
                        <td>Language</td>
                        <td>Edit schedule &rarr; Details</td>
                        <td>Sets the language the parser preserves in the event text, with English translations stored alongside</td>
                    </tr>
                    <tr>
                        <td>Import Form Fields</td>
                        <td>Edit schedule &rarr; Settings &rarr; Advanced (hosted service)</td>
                        <td>A toggle per extra field (short description, description, price, coupon code and its discount, registration URL, category and sub-schedules) that chooses which ones appear on each parsed card, each with a <strong class="text-gray-900 dark:text-white">Required</strong> tick box for fields that must be filled in before the event can be saved</td>
                    </tr>
                    <tr>
                        <td>Agenda prompt <x-doc-badge plan="enterprise" /></td>
                        <td>Scan Agenda page &rarr; Edit prompt</td>
                        <td>Free-text instructions for agenda scanning, which creates event parts rather than events. Can be saved as the default for the schedule</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Custom field prompts are worded like instructions to a person. The examples offered in the field editor read "Extract the dress code from the event details" and "Identify the target age group for this event".</p>
        </div>
    </section>

    <div class="doc-callout doc-callout-plan">
        <div class="doc-callout-title">More AI on your events <x-doc-badge plan="enterprise" /></div>
        <p>AI can also generate a flyer image from event details you already have, and write an event description and category for you. See <a href="{{ route('marketing.docs.creating_events') }}#ai-flyer" class="doc-link">AI Flyer Generation</a> and <a href="{{ route('marketing.docs.creating_events') }}#ai-details-generator" class="doc-link">AI Details Generator</a>.</p>
    </div>

    <!-- See Also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            See Also
        </h2>
        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - Add events by hand and configure event settings</li>
            <li><a href="{{ route('marketing.docs.creating_events') }}#whatsapp" class="doc-link">Creating Events via WhatsApp</a> - Send a message or flyer to a WhatsApp number and let AI create the event (Enterprise)</li>
            <li><a href="{{ route('marketing.docs.scan_agenda') }}" class="doc-link">Scan Agenda</a> - Use AI to read a printed agenda and create event parts (Enterprise)</li>
            <li><a href="{{ route('marketing.docs.creating_schedules') }}#auto-import" class="doc-link">Auto Import</a> - Have a selfhosted install import events every day from a list of event URLs</li>
            <li><a href="{{ route('marketing.docs.creating_schedules') }}#customize-custom-fields" class="doc-link">Custom Fields</a> - Define your own event fields, with AI prompts for each (Pro)</li>
        </ul>
    </section>


    <x-slot:schema>
        <script type="application/ld+json" {!! nonce_attr() !!}>
        {
            "@context": "https://schema.org",
            "@type": "HowTo",
            "name": "How to Import Events Using AI in Event Schedule",
            "description": "Learn how to import events by pasting a link to an events page or calendar, pasting event text, or adding a flyer image.",
            "totalTime": "PT3M",
            "step": [
                {
                    "@type": "HowToStep",
                    "name": "Open the Import Page",
                    "text": "Open your schedule in the admin panel, click Actions, then Import Events.",
                    "url": "{{ url(route('marketing.docs.ai_import')) }}#ai-import"
                },
                {
                    "@type": "HowToStep",
                    "name": "Paste a Link or Text, or Add an Image",
                    "text": "Paste a link to your events page or calendar, type or paste the event text, or add a flyer image, then click the Read button at the end of the box.",
                    "url": "{{ url(route('marketing.docs.ai_import')) }}#text-import"
                },
                {
                    "@type": "HowToStep",
                    "name": "Review and Add",
                    "text": "Review what came back, correct anything that is wrong, tick the events you want and click Add events.",
                    "url": "{{ url(route('marketing.docs.ai_import')) }}#choosing-events"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
