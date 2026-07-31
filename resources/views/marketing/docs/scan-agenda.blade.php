<x-docs-page
    key="scan-agenda"
    plan="enterprise"
    description="Learn how to photograph a printed agenda with your phone and let AI turn it into event parts in Event Schedule."
    lede="Photograph a printed agenda with your phone camera and let AI turn it into the event's agenda parts."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#getting-started">Getting Started</x-doc-nav-link>
        <x-doc-nav-link href="#how-it-works">How It Works</x-doc-nav-link>
        <x-doc-nav-link href="#custom-prompt">Custom AI Prompt</x-doc-nav-link>
        <x-doc-nav-link href="#tips">Tips</x-doc-nav-link>
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
            Scan Agenda points your phone camera at a printed agenda, flyer, program or setlist and uses AI to turn each line into an <strong class="text-gray-900 dark:text-white">event part</strong>. You review the parsed list on screen, fix anything the AI misread, and save it to one of your events. Parsing runs on whichever AI provider the installation is configured for, Google Gemini by default or OpenAI.
        </p>

        <x-doc-screenshot id="scan-agenda--page" alt="Scan agenda page" loading="eager" />

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Scanning is Enterprise, the agenda itself is not</div>
            <p>
                <x-doc-badge plan="free" /> Event parts are a free feature. Every plan can add, name, time, describe, reorder and delete agenda parts by hand in the <strong class="text-gray-900 dark:text-white">Agenda</strong> section of the event form.
            </p>
            <p class="mt-2">
                <x-doc-badge plan="enterprise" /> Only the AI that reads an agenda for you requires an Enterprise plan. A selfhosted install counts as Enterprise, so nothing here is held back there.
            </p>
        </div>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Capability</th>
                        <th>Where</th>
                        <th>Plan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Add, edit, time and reorder parts by hand</span></td>
                        <td>Event form &rarr; Agenda</td>
                        <td><x-doc-badge plan="free" /></td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Show times / Show description</span></td>
                        <td>Event form &rarr; Agenda, and they apply to the whole schedule</td>
                        <td><x-doc-badge plan="free" /></td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Import from Image / Import from Text</span></td>
                        <td>Event form &rarr; Agenda</td>
                        <td><x-doc-badge plan="enterprise" /></td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Scan Agenda (live camera)</span></td>
                        <td>Schedule page &rarr; Actions menu, on phone-sized screens only</td>
                        <td><x-doc-badge plan="enterprise" /></td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Save agenda image on the event page</span></td>
                        <td>Scan screen, or Event form &rarr; Agenda</td>
                        <td><x-doc-badge plan="enterprise" /></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Typical uses:
        </p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Conference organizers</strong> - Turn a printed program of sessions into a timed agenda</li>
            <li><strong class="text-gray-900 dark:text-white">Venues</strong> - Capture a lineup from a poster or door flyer</li>
            <li><strong class="text-gray-900 dark:text-white">Talent</strong> - Photograph a handwritten setlist and publish it as a running order</li>
        </ul>

        <h3 class="doc-subheading">What you need</h3>
        <ul class="doc-list mb-6">
            <li>An <strong class="text-gray-900 dark:text-white">Enterprise</strong> plan on the schedule you are scanning for, or a selfhosted install</li>
            <li>A Google Gemini or OpenAI key configured on the installation. Without one the menu entry is hidden and the event form's Agenda section drops its AI controls, which matters mainly for selfhosted deployments</li>
            <li>A device with a camera, and camera permission granted to the browser. The scan screen has no file picker</li>
            <li>At least one event on the schedule that does not have agenda parts yet</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Daily limit</div>
            <p>On the hosted service each schedule can run <strong class="text-gray-900 dark:text-white">10 agenda parses per day</strong>, and the allowance is shared with <strong class="text-gray-900 dark:text-white">Import from Image</strong> and <strong class="text-gray-900 dark:text-white">Import from Text</strong> in the event form, since all three call the same parser. Once the day's allowance is used the parse is refused until the next day. Selfhosted installs have no limit.</p>
        </div>
    </section>

    <!-- Getting Started -->
    <section id="getting-started" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
            </svg>
            Getting Started
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Scan Agenda opens from the schedule's own page in the admin panel:
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open the schedule in the admin panel</li>
            <li>Open the <strong class="text-gray-900 dark:text-white">Actions</strong> menu at the top of the page</li>
            <li>Choose <strong class="text-gray-900 dark:text-white">Scan Agenda</strong></li>
        </ol>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">It is a phone-sized entry</div>
            <p>Because the flow drives the device camera, <strong class="text-gray-900 dark:text-white">Scan Agenda</strong> is listed in the Actions menu only on small screens. In a desktop-width window it is hidden. To work from an agenda file on a computer, open the event and use <strong class="text-gray-900 dark:text-white">Import from Image</strong> in the Agenda section instead.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">
            The entry only appears for schedule owners and editors on an Enterprise plan. On the hosted service, other plans see an upgrade prompt in its place.
        </p>

        <h3 class="doc-subheading">Choosing the event</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The parts you scan always belong to one event, chosen in the selector at the top of the scan screen. Event Schedule preselects a likely candidate:
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>The most recent event that started within the past month and has no agenda parts yet</li>
            <li>If there is none, the next upcoming event with no agenda parts</li>
            <li>If neither exists, the first event in the dropdown</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            The dropdown lists up to 50 of the schedule's events that have no parts yet, past and upcoming together, ordered by date with the latest first and each shown with its image and date. Tap one to switch. If the schedule has no such event you see "No events found. Create an event first, then scan its agenda."
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Events that already have an agenda</div>
            <p>An event that already has parts is not offered in the selector. To rebuild its agenda from a photo, remove the existing parts in the event form first, or use <strong class="text-gray-900 dark:text-white">Import from Image</strong> there, which appends the parsed parts to the ones already listed.</p>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            How It Works
        </h2>
        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong class="text-gray-900 dark:text-white">Confirm the event</strong> - Check the event shown in the selector at the top, or pick another one</li>
            <li><strong class="text-gray-900 dark:text-white">Start Camera</strong> - Tap the button and allow camera access. If the device offers more than one camera you choose it in the <strong class="text-gray-900 dark:text-white">Select Camera</strong> dialog; the choice is remembered on that device, and <strong class="text-gray-900 dark:text-white">Change Camera</strong> switches later. Once you have granted access the camera starts on its own the next time you open the screen on that device</li>
            <li><strong class="text-gray-900 dark:text-white">Set the options</strong> - Below the preview, <strong class="text-gray-900 dark:text-white">Edit Prompt</strong> adds instructions for the AI and <strong class="text-gray-900 dark:text-white">Save agenda image</strong> keeps the photo and shows it on the public event page above the agenda</li>
            <li><strong class="text-gray-900 dark:text-white">Capture</strong> - Tap the round shutter button under the preview. The frame is sent for parsing, so line the agenda up before you tap</li>
            <li><strong class="text-gray-900 dark:text-white">AI reads the photo</strong> - Each agenda line comes back as one part, in the original order</li>
            <li><strong class="text-gray-900 dark:text-white">Review and edit</strong> - Every part is a card you can retype. The X button at the end of a card removes it, and <strong class="text-gray-900 dark:text-white">+ Add</strong> appends an empty one for anything the AI missed</li>
            <li><strong class="text-gray-900 dark:text-white">Reorder</strong> - Drag a card, using the grip at its start, and drop it where it belongs</li>
            <li><strong class="text-gray-900 dark:text-white">Save or retake</strong> - Tap <strong class="text-gray-900 dark:text-white">Save</strong> in the bar at the bottom to write the list to the event and jump to the agenda on the public event page. <strong class="text-gray-900 dark:text-white">Retake</strong> discards the parsed list and returns to the camera screen, where you tap <strong class="text-gray-900 dark:text-white">Start Camera</strong> again</li>
        </ol>

        <h3 class="doc-subheading">What the AI extracts</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>What comes back</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Name</span></td>
                        <td>The title of the session, act or song. Always filled in, required before a part can be saved, and capped at 255 characters</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Description</span></td>
                        <td>Supporting detail such as a speaker or performer name, when the agenda shows one. Up to 1,000 characters</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Start time</span></td>
                        <td>24-hour <code class="doc-inline-code">HH:MM</code>, left empty on an agenda with no times, such as a setlist</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">End time</span></td>
                        <td>24-hour <code class="doc-inline-code">HH:MM</code> where the agenda gives one</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">
            On the review screen the two time boxes are plain text fields rather than pickers, so you can retype a time straight over what the AI read. The event form's Agenda section gives the same parts a time picker if you would rather correct them there.
        </p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Saving replaces the agenda</div>
            <p>Save writes the list on screen as the event's complete agenda: any parts the event already had are replaced, and parts do not accumulate across scans. Scan the whole agenda in one photo where you can, and add anything left over by hand in the event form.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Times and descriptions follow the schedule's settings</div>
            <p>The time and description fields appear on the review screen only when <strong class="text-gray-900 dark:text-white">Show times</strong> and <strong class="text-gray-900 dark:text-white">Show description</strong> are enabled for the schedule, in the event form's Agenda section. When one is off, that field is hidden here and saved empty, even if the AI read a value for it.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            On the public event page the saved parts render as a vertical timeline when at least one part has a start time, and as a numbered running order when none of them do.
        </p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">If nothing is found</div>
            <p>When the AI cannot read any items you get a "No events found" notice and go back to the camera, so you can retake the photo or add a prompt. With <strong class="text-gray-900 dark:text-white">Save agenda image</strong> enabled you land on the review screen instead, with the photo shown above an empty list you can fill in by hand. Tapping <strong class="text-gray-900 dark:text-white">Save</strong> on a genuinely empty list changes nothing on the event; it just takes you to the event page.</p>
        </div>
    </section>

    <!-- Custom AI Prompt -->
    <section id="custom-prompt" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
            Custom AI Prompt
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The prompt is optional: the built-in instructions handle a normal agenda, program or setlist. Add your own when the layout is unusual or the AI keeps misreading the same thing.
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>With the camera running, tap <strong class="text-gray-900 dark:text-white">Edit Prompt</strong></li>
            <li>Type your instructions in the <strong class="text-gray-900 dark:text-white">AI Prompt</strong> box, up to 500 characters</li>
            <li>Optionally tick <strong class="text-gray-900 dark:text-white">Save as default</strong></li>
            <li>Tap <strong class="text-gray-900 dark:text-white">Done</strong> and capture the photo</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Instructions that work well:
        </p>
        <ul class="doc-list mb-6">
            <li>"Each part name should include the artist name in parentheses"</li>
            <li>"Times are in 24-hour format"</li>
            <li>"Ignore the header and the lunch breaks"</li>
            <li>"The left column is the time and the right column is the session title"</li>
        </ul>

        <h3 class="doc-subheading">Where the prompt is stored</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Save as default</strong> stores the prompt on the schedule, so it is prefilled for later scans and for AI imports in the event form. It stores the <strong class="text-gray-900 dark:text-white">Save agenda image</strong> setting on the schedule at the same time</li>
            <li>Every scan also stores the prompt on the event you scanned, whether or not you saved it as the default</li>
            <li>The scan screen opens with the selected event's own prompt when it has one, and falls back to the schedule default. "No prompt set" means neither exists yet</li>
            <li>The same <strong class="text-gray-900 dark:text-white">AI Prompt</strong> field, with the same 500-character limit and the same <strong class="text-gray-900 dark:text-white">Save as default</strong> box, sits in the event form's Agenda section</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300">
            The prompt only shapes what the AI reads. It has no effect on parts you type in by hand.
        </p>
    </section>

    <!-- Tips -->
    <section id="tips" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
            </svg>
            Tips
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            For the best results when scanning:
        </p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Use the rear camera</strong> - It is usually the sharper one. If the preview looks soft, switch with <strong class="text-gray-900 dark:text-white">Change Camera</strong></li>
            <li><strong class="text-gray-900 dark:text-white">Good lighting</strong> - Light the page evenly and keep your own shadow off it</li>
            <li><strong class="text-gray-900 dark:text-white">Flat surface</strong> - Flatten creases and curl so lines do not bend out of shape</li>
            <li><strong class="text-gray-900 dark:text-white">Full frame</strong> - Fit the whole agenda in one photo. Because saving replaces the agenda, one photo per event is much easier than two</li>
            <li><strong class="text-gray-900 dark:text-white">Readable text</strong> - Fill the frame with the agenda so the smallest line is still legible, and avoid blurry shots</li>
            <li><strong class="text-gray-900 dark:text-white">Complex layouts</strong> - For multi-column programs or handwriting, describe the layout in the prompt before you shoot</li>
        </ul>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">The scan is a starting point</div>
            <p>A missed line or a mistyped time costs seconds to fix on the review screen, and everything stays editable afterwards in the event form's Agenda section. Treat the scan as the draft that saves you the typing, not as the final word.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Camera blocked?</div>
            <p>If the browser refuses the camera, the page names the cause: permission denied, no camera found, the camera being used by another app, or an unknown error. For a denied permission it also lists the steps to re-enable it, with separate instructions for phones and computers. <strong class="text-gray-900 dark:text-white">Try Again</strong> retries once you have fixed it.</p>
        </div>
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
            <li><a href="{{ route('marketing.docs.creating_events') }}#agenda" class="doc-link">Creating Events: Agenda</a> - Build and edit event parts by hand on any plan</li>
            <li><a href="{{ route('marketing.docs.ai_import') }}" class="doc-link">AI Import</a> - Create whole events from pasted text or a flyer image</li>
            <li><a href="{{ route('marketing.docs.selfhost.ai') }}" class="doc-link">Selfhost: AI Features</a> - Configure the Gemini or OpenAI key that the parser needs</li>
        </ul>
    </section>


    <x-slot:schema>
        <script type="application/ld+json" {!! nonce_attr() !!}>
        {
            "@context": "https://schema.org",
            "@type": "HowTo",
            "name": "How to Scan an Agenda in Event Schedule",
            "description": "Learn how to photograph a printed agenda with your phone and let AI turn it into event parts in Event Schedule.",
            "totalTime": "PT5M",
            "step": [
                {
                    "@type": "HowToStep",
                    "name": "Open Scan Agenda",
                    "text": "Open the schedule in the admin panel on a phone, open the Actions menu, and choose Scan Agenda.",
                    "url": "{{ url(route('marketing.docs.scan_agenda')) }}#getting-started"
                },
                {
                    "@type": "HowToStep",
                    "name": "Pick the event and start the camera",
                    "text": "Confirm the event in the selector, tap Start Camera, and allow camera access.",
                    "url": "{{ url(route('marketing.docs.scan_agenda')) }}#getting-started"
                },
                {
                    "@type": "HowToStep",
                    "name": "Capture the agenda",
                    "text": "Frame the whole printed agenda and tap the shutter button so the AI can read it.",
                    "url": "{{ url(route('marketing.docs.scan_agenda')) }}#how-it-works"
                },
                {
                    "@type": "HowToStep",
                    "name": "Review and save",
                    "text": "Edit the parsed parts, add anything missing, drag them into order, and tap Save to write them to the event.",
                    "url": "{{ url(route('marketing.docs.scan_agenda')) }}#how-it-works"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
