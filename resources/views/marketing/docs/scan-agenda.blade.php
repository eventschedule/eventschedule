<x-marketing-layout>
    <x-slot name="title">Scan Agenda Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Scan Agenda</x-slot>
    <x-slot name="description">Learn how to use AI to scan a printed agenda and automatically create event parts in Event Schedule.</x-slot>
    <x-slot name="socialImage">social/docs.png</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Scan Agenda Documentation - Event Schedule",
        "description": "Learn how to use AI to scan a printed agenda and automatically create event parts in Event Schedule.",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ config('app.url') }}/images/light_logo.png",
                "width": 712,
                "height": 140
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}"
        },
        "datePublished": "2024-01-01",
        "dateModified": "2026-02-01"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Scan Agenda" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Scan Agenda</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Use AI to scan a photo of a printed agenda and automatically create event parts on your schedule.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-white dark:bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <a href="#overview" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Overview</a>
                        <a href="#getting-started" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Getting Started</a>
                        <a href="#how-it-works" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">How It Works</a>
                        <a href="#custom-prompt" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Customizing the AI Prompt</a>
                        <a href="#tips" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Tips</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">Overview</h2>
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
                            <h2 class="doc-heading">Getting Started</h2>
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
                            <h2 class="doc-heading">How It Works</h2>
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
                            <h2 class="doc-heading">Customizing the AI Prompt</h2>
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
                            <h2 class="doc-heading">Tips</h2>
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
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events and manage event parts manually</li>
                                <li><a href="{{ route('marketing.docs.event_graphics') }}" class="text-cyan-400 hover:text-cyan-300">Event Graphics</a> - Generate shareable graphics for your events</li>
                            </ul>
                        </section>

                        @include('marketing.docs.partials.navigation')
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')

    <!-- HowTo Schema for Rich Snippets -->
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
</x-marketing-layout>
