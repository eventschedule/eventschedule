<x-docs-page
    key="ai-import"
    description="Learn how to import events using AI. Paste event text or add a flyer image and let AI extract the event details automatically."
    lede="Save hours of manual data entry. Paste event text or add a flyer image and let AI extract the event details automatically."
>
    <x-slot:toc>
        <x-doc-nav-group label="AI Import" href="#ai-import" expanded>
            <x-doc-nav-link href="#text-import">From Text</x-doc-nav-link>
            <x-doc-nav-link href="#image-import">From Images/Flyers</x-doc-nav-link>
            <x-doc-nav-link href="#custom-prompts">Custom AI Prompts</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- AI Import -->
    <section id="ai-import" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
            </svg>
            Let AI Do the Heavy Lifting
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">AI import takes unstructured event information and turns it into events you review before saving. It accepts two kinds of input: <strong class="text-gray-900 dark:text-white">text</strong> you type or paste, and an <strong class="text-gray-900 dark:text-white">image</strong> such as a flyer or poster. The AI extracts the name, date and time, venue, description, price and more, then shows one editable card per event it found. Nothing is added to your schedule until you save it.</p>

        <x-doc-screenshot id="creating-events--import" alt="Import events page" loading="eager" />

        <h3 class="doc-subheading">Opening the Import Page</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open your schedule in the admin panel</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Actions</strong> in the top right</li>
            <li>Choose <strong class="text-gray-900 dark:text-white">Import Events</strong></li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6"><strong class="text-gray-900 dark:text-white">More Options</strong> at the top of the import page opens the <strong class="text-gray-900 dark:text-white">Import Events</strong> source list, which holds AI import alongside <strong class="text-gray-900 dark:text-white">Import from Eventbrite</strong> <x-doc-badge plan="pro" />, the connector that pulls your events, tickets and venues from a connected Eventbrite account.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Text or image, not a link</div>
            <p>The AI only reads the text and the image you hand it. It does not go and fetch a web page, so pasting a URL on its own will not produce an event: copy the listing text itself, or add the flyer image. A ticket link found <em>inside</em> the text is kept as the event's registration URL, and the link's own preview image is used as the event image when the submission had no image of its own.</p>
        </div>

        <div class="doc-callout doc-callout-tip mt-4">
            <div class="doc-callout-title">AI-Powered</div>
            <p>Parsing is done by Google Gemini or by OpenAI, whichever is configured. On the hosted service it is ready to use. On a selfhosted install the import page shows a <strong class="text-gray-900 dark:text-white">Setup Required: Gemini API Key</strong> panel until <code class="doc-inline-code">GEMINI_API_KEY</code> or <code class="doc-inline-code">OPENAI_API_KEY</code> is set, see <a href="{{ route('marketing.docs.selfhost.ai') }}" class="doc-link">AI Setup</a>.</p>
        </div>

        <h3 class="doc-subheading">What You Can Send</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Text</strong> - Up to 10,000 characters per submission</li>
            <li><strong class="text-gray-900 dark:text-white">Image</strong> - One JPG, PNG, GIF or WebP file of up to 10 MB per submission</li>
            <li><strong class="text-gray-900 dark:text-white">Both together</strong> - A flyer plus a line of text covering whatever the flyer leaves out</li>
        </ul>

        <h3 class="doc-subheading">Daily Limits</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Each submission counts as one AI request, whether it is text, an image or both, and one request can return several events. The allowance is counted per schedule per day on the hosted service. When it runs out the page asks you to try again tomorrow.</p>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">The trial row is checked first, so a schedule inside a paid-plan trial gets the trial allowance rather than its plan's. Separately from the daily count there is a short-term ceiling of 30 submissions a minute, which returns "Too many requests, please wait a minute and try again" and clears on its own.</p>
        <p class="text-gray-600 dark:text-gray-300">A venue or curator schedule that accepts event requests without requiring visitors to have an account shows those visitors this same AI box instead of a structured form. See <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-requests" class="doc-link">Requests</a> for the submission form options.</p>
    </section>

    <!-- Text Import -->
    <section id="text-import" class="doc-section">
        <h3 class="doc-subheading">Importing from Text</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Copy event information from an email, a website listing, a social media post or a message, and paste it into the import box.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Type or paste the event text into the box, which reads <em>Type event details or drag &amp; drop an image here</em> while it is empty</li>
            <li>Click the <strong class="text-gray-900 dark:text-white">arrow button</strong> at the end of the box to submit it, or press <strong class="text-gray-900 dark:text-white">Ctrl+Enter</strong>. Enter on its own starts a new line</li>
            <li>Review the card for each event the AI found and correct anything it got wrong</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Save</strong> on a card to create that event. When more than one event was parsed, <strong class="text-gray-900 dark:text-white">Save All</strong> appears above the cards</li>
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
            <li><strong class="text-gray-900 dark:text-white">Sensible dates only</strong> - A parsed date that is more than three days in the past, or more than about two months away, is left blank for you to fill in rather than guessed</li>
            <li><strong class="text-gray-900 dark:text-white">Other languages</strong> - For a schedule whose language is not English, the AI keeps the original language and adds English translations alongside it</li>
            <li><strong class="text-gray-900 dark:text-white">Which fields appear</strong> - Name, date and time, and venue are always on the card. The extra fields (short description, description, price, coupon code, registration URL, category and sub-schedule) are switched on per schedule, and can be marked required. The sub-schedule field only appears once the schedule has sub-schedules</li>
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
    </section>

    <!-- Image Import -->
    <section id="image-import" class="doc-section">
        <h3 class="doc-subheading">Importing from Images/Flyers</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Give the AI a flyer, poster or screenshot and it reads the text out of the image.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Add the image in whichever way suits you: click the <strong class="text-gray-900 dark:text-white">+</strong> button to pick a file, paste an image from your clipboard into the box, or drag and drop the file onto the box</li>
            <li>A thumbnail appears in the corner of the box. Use the red x on it to remove the image if you picked the wrong one</li>
            <li>Optionally type a note in the box as well, for anything the flyer leaves out</li>
            <li>Click the <strong class="text-gray-900 dark:text-white">arrow button</strong> to submit</li>
            <li>Review the card, then click <strong class="text-gray-900 dark:text-white">Save</strong></li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The image you submitted is carried over as the event's image and shown beside the parsed fields, so a flyer import needs no separate upload. You can remove it there, or drop a different image onto that panel before saving. The one exception is text that also contains a ticket link: that link's own preview image wins, because it is usually the publisher's artwork.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Image Tips</div>
            <p>One image per submission, in JPG, PNG, GIF or WebP, up to 10 MB. Use a clear, high-contrast picture where the text is large enough to read: the AI can only extract what is legible. Photograph the flyer flat and in full frame rather than at an angle.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mt-6">Photographing a printed <em>agenda</em> to create timed parts inside one existing event is a separate feature. See <a href="{{ route('marketing.docs.scan_agenda') }}" class="doc-link">Scan Agenda</a> <x-doc-badge plan="enterprise" />.</p>
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
                        <td>A toggle per extra field (short description, description, price, coupon code, registration URL, category and sub-schedules) that chooses which ones appear on each parsed card, each with a <strong class="text-gray-900 dark:text-white">Required</strong> tick box for fields that must be filled in before the event can be saved</td>
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
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            See Also
        </h2>
        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - Add events by hand and configure event settings</li>
            <li><a href="{{ route('marketing.docs.creating_events') }}#whatsapp" class="doc-link">Creating Events via WhatsApp</a> - Send a message or flyer to a WhatsApp number and let AI create the event (Enterprise)</li>
            <li><a href="{{ route('marketing.docs.scan_agenda') }}" class="doc-link">Scan Agenda</a> - Use AI to read a printed agenda and create event parts (Enterprise)</li>
            <li><a href="{{ route('marketing.docs.creating_schedules') }}#auto-import" class="doc-link">Auto Import</a> - Have a selfhosted install pull events from a list of URLs or cities on a schedule</li>
            <li><a href="{{ route('marketing.docs.creating_schedules') }}#customize-custom-fields" class="doc-link">Custom Fields</a> - Define your own event fields, with AI prompts for each (Pro)</li>
        </ul>
    </section>


    <x-slot:schema>
        <script type="application/ld+json" {!! nonce_attr() !!}>
        {
            "@context": "https://schema.org",
            "@type": "HowTo",
            "name": "How to Import Events Using AI in Event Schedule",
            "description": "Learn how to import events using AI by pasting event text or adding a flyer image.",
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
                    "name": "Paste Text or Add an Image",
                    "text": "Type or paste the event text into the box, or add a flyer image, then click the arrow button to submit it.",
                    "url": "{{ url(route('marketing.docs.ai_import')) }}#text-import"
                },
                {
                    "@type": "HowToStep",
                    "name": "Review and Save",
                    "text": "Review the card for each event the AI found, correct anything it got wrong, and click Save.",
                    "url": "{{ url(route('marketing.docs.ai_import')) }}#text-import"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
