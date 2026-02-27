<x-marketing-layout>
    <x-slot name="title">AI Import - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">AI Import</x-slot>
    <x-slot name="description">Learn how to import events using AI. Paste text or upload images and let AI extract event details automatically.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "AI Import - Event Schedule",
        "description": "Learn how to import events using AI. Paste text or upload images and let AI extract event details automatically.",
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
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-amber-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-yellow-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="AI Import" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">AI Import</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Save hours of manual data entry. Paste text or upload images and let AI extract event details automatically.
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
                        <a href="#ai-import" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">AI Import</a>
                        <a href="#text-import" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors pl-6">From Text</a>
                        <a href="#image-import" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors pl-6">From Images/Flyers</a>
                        <a href="#custom-prompts" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors pl-6">Custom AI Prompts</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- AI Import -->
                        <section id="ai-import" class="doc-section">
                            <h2 class="doc-heading">Let AI Do the Heavy Lifting <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 ml-2">Enterprise</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Save hours of manual data entry. Paste any event text - emails, social media posts, website listings, or even flyer descriptions - and watch it transform into a ready-to-publish event in seconds.</p>

                            <x-doc-screenshot id="creating-events--import" alt="Import events page" loading="eager" />

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">AI-Powered</div>
                                <p>AI import uses Google Gemini to intelligently extract event name, date, time, venue, description, and more from unstructured text and images.</p>
                            </div>
                        </section>

                        <!-- Text Import -->
                        <section id="text-import" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Importing from Text</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Copy and paste event information from any source - emails, websites, social media posts - and AI will extract the event details.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule</strong> and click <strong class="text-gray-900 dark:text-white">"Import"</strong></li>
                                <li>Paste your event text into the text box</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Parse Events"</strong></li>
                                <li>Review the extracted events and make any corrections</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Import"</strong> to add them to your schedule</li>
                            </ol>

                            <div class="grid md:grid-cols-2 gap-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Example Input</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 code-block">
                                        Live Jazz Night<br>
                                        Friday, March 15th at 8pm<br>
                                        The Blue Note, 123 Main Street<br>
                                        Featuring the John Smith Trio<br>
                                        Tickets: $20
                                    </p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-emerald-500/30">
                                    <h4 class="font-semibold text-emerald-400 mb-2">AI Extracts</h4>
                                    <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
                                        <li><strong class="text-gray-900 dark:text-white">Name:</strong> Live Jazz Night</li>
                                        <li><strong class="text-gray-900 dark:text-white">Date:</strong> March 15</li>
                                        <li><strong class="text-gray-900 dark:text-white">Time:</strong> 8:00 PM</li>
                                        <li><strong class="text-gray-900 dark:text-white">Venue:</strong> The Blue Note</li>
                                        <li><strong class="text-gray-900 dark:text-white">Address:</strong> 123 Main Street</li>
                                        <li><strong class="text-gray-900 dark:text-white">Description:</strong> Featuring the John Smith Trio</li>
                                        <li><strong class="text-gray-900 dark:text-white">Price:</strong> $20</li>
                                    </ul>
                                </div>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300">Review the extracted details, make any corrections, and click Import. You can process multiple events at once.</p>
                        </section>

                        <!-- Image Import -->
                        <section id="image-import" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Importing from Images/Flyers</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Upload an event flyer or poster and AI will read the text and extract event information.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule</strong> and click <strong class="text-gray-900 dark:text-white">"Import"</strong></li>
                                <li>Upload an image file (JPG, PNG, etc.)</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Parse Events"</strong></li>
                                <li>Review the extracted events</li>
                                <li>The uploaded image can automatically become the event's featured image</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Import"</strong> to add them to your schedule</li>
                            </ol>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Image Tips</div>
                                <p>For best results, use clear, high-contrast images where text is easily readable. The AI works best with images that have legible text.</p>
                            </div>
                        </section>

                        <!-- Custom AI Prompts -->
                        <section id="custom-prompts" class="doc-section">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Custom AI Prompts</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">You can add custom instructions to help AI understand your specific event format. This is useful when your events use a non-standard layout.</p>

                            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Example Prompt</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 code-block">
                                    Each line is a session. Format: time - speaker - topic. Ignore lunch breaks.
                                </p>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300">You can set a custom prompt per import, or set a default prompt for your entire schedule under <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong>.</p>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events and configure event settings</li>
                                <li><a href="{{ route('marketing.docs.scan_agenda') }}" class="text-cyan-400 hover:text-cyan-300">Scan Agenda</a> - Use AI to scan a printed agenda and create event parts</li>
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
        "name": "How to Import Events Using AI in Event Schedule",
        "description": "Learn how to import events using AI by pasting text or uploading images.",
        "totalTime": "PT3M",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Open the Import Page",
                "text": "Go to Admin Panel, then Schedule, and click Import.",
                "url": "{{ url(route('marketing.docs.ai_import')) }}#ai-import"
            },
            {
                "@type": "HowToStep",
                "name": "Paste Text or Upload an Image",
                "text": "Paste event text from any source or upload a flyer image.",
                "url": "{{ url(route('marketing.docs.ai_import')) }}#text-import"
            },
            {
                "@type": "HowToStep",
                "name": "Review and Import",
                "text": "Review the AI-extracted event details, make corrections if needed, and click Import.",
                "url": "{{ url(route('marketing.docs.ai_import')) }}#text-import"
            }
        ]
    }
    </script>
</x-marketing-layout>
