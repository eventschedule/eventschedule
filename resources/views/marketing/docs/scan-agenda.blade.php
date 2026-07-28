<x-docs-page
    key="scan-agenda"
    description="Learn how to use AI to scan a printed agenda and automatically create event parts in Event Schedule."
    lede="Use AI to scan a photo of a printed agenda and automatically create event parts on your schedule."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#getting-started">Getting Started</x-doc-nav-link>
        <x-doc-nav-link href="#how-it-works">How It Works</x-doc-nav-link>
        <x-doc-nav-link href="#custom-prompt">Customizing the AI Prompt</x-doc-nav-link>
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
            Scan Agenda is an Enterprise feature that uses AI (powered by Google Gemini) to read a photo of a printed agenda, flyer, or setlist and automatically create event parts from it. Instead of manually typing each item, simply take a photo and let the AI do the work.
        </p>

        <x-doc-screenshot id="scan-agenda--page" alt="Scan agenda page" loading="eager" />

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            This is especially useful for:
        </p>
        <ul class="doc-list mb-6">
            <li><strong>Conference organizers</strong> - Quickly digitize a printed conference program with multiple sessions</li>
            <li><strong>Venues</strong> - Import a lineup from a poster or flyer</li>
            <li><strong>Event planners</strong> - Convert a handwritten or printed agenda into your schedule</li>
        </ul>
        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>Scan Agenda requires an Enterprise plan and is available from the schedule admin panel.</p>
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
            To access Scan Agenda:
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to your schedule's admin panel</li>
            <li>Click the <strong>more menu</strong> (three dots) in the top right</li>
            <li>Select <strong>Scan Agenda</strong> from the dropdown</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            You will be taken to the Scan Agenda page where you can photograph a printed agenda.
        </p>
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
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The scan process has a few simple steps:
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong>Take a photo</strong> - Use your device camera to take a photo of the printed agenda</li>
            <li><strong>AI parses the content</strong> - The AI reads the image and extracts individual agenda items, including names, times, and descriptions where available</li>
            <li><strong>Review and edit</strong> - The parsed results appear as a list of event parts. You can edit names, times, and descriptions, or remove any items that were not parsed correctly</li>
            <li><strong>Reorder with drag and drop</strong> - Drag items into the correct order if needed</li>
            <li><strong>Save to your event</strong> - Select the event to add the parts to and save</li>
        </ol>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>You can scan multiple photos for the same event. Each scan adds new parts that you can review before saving.</p>
        </div>
    </section>

    <!-- Customizing the AI Prompt -->
    <section id="custom-prompt" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
            Customizing the AI Prompt
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            If your agenda uses a specific format or contains non-standard content, you can customize the AI prompt to get better results:
        </p>
        <ul class="doc-list mb-6">
            <li><strong>Custom instructions</strong> - Add instructions like "ignore the header" or "times are in 24-hour format" to help the AI interpret your agenda correctly</li>
            <li><strong>Save as default</strong> - Save your custom prompt as the default for future scans on this schedule, so you do not need to re-enter it each time</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The custom prompt is optional. The default prompt works well for most standard agenda formats.
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
            For the best results when scanning agendas:
        </p>
        <ul class="doc-list mb-6">
            <li><strong>Good lighting</strong> - Make sure the agenda is well-lit and the text is clearly visible</li>
            <li><strong>Flat surface</strong> - Place the agenda on a flat surface to avoid distortion from creases or curves</li>
            <li><strong>Full frame</strong> - Capture the entire agenda in the photo. The AI works best when it can see all items at once</li>
            <li><strong>Readable text</strong> - Ensure the text is large enough to be legible in the photo. Avoid blurry or low-resolution images</li>
            <li><strong>Complex formats</strong> - For agendas with multiple columns or unusual layouts, consider using a custom prompt to guide the AI</li>
        </ul>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>If the AI misses some items or gets details wrong, you can always edit the results before saving. The scan is a starting point that saves you from entering everything manually.</p>
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
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - Add events and manage event parts manually</li>
            <li><a href="{{ route('marketing.docs.event_graphics') }}" class="doc-link">Event Graphics</a> - Generate shareable graphics for your events</li>
        </ul>
    </section>


    <x-slot:schema>
        <script type="application/ld+json" {!! nonce_attr() !!}>
        {
            "@context": "https://schema.org",
            "@type": "HowTo",
            "name": "How to Scan an Agenda in Event Schedule",
            "description": "Learn how to use AI to scan a printed agenda and automatically create event parts in Event Schedule.",
            "totalTime": "PT5M",
            "step": [
                {
                    "@type": "HowToStep",
                    "name": "Open Scan Agenda",
                    "text": "Go to your schedule's admin panel, click the more menu, and select Scan Agenda.",
                    "url": "{{ url(route('marketing.docs.scan_agenda')) }}#getting-started"
                },
                {
                    "@type": "HowToStep",
                    "name": "Take a Photo",
                    "text": "Use your device camera to take a photo of your printed agenda.",
                    "url": "{{ url(route('marketing.docs.scan_agenda')) }}#how-it-works"
                },
                {
                    "@type": "HowToStep",
                    "name": "Review and Save",
                    "text": "Review the AI-parsed results, edit as needed, reorder items with drag and drop, and save to your event.",
                    "url": "{{ url(route('marketing.docs.scan_agenda')) }}#how-it-works"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
