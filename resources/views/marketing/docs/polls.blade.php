<x-marketing-layout>
    <x-slot name="title">Event Polls Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Event Polls</x-slot>
    <x-slot name="description">Learn how to create interactive polls on your events in Event Schedule.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Event Polls Documentation - Event Schedule",
        "description": "Learn how to create interactive polls on your events in Event Schedule.",
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
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-indigo-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Event Polls" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Event Polls</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Add interactive multiple-choice questions to your events and let your guests vote on the options that matter most.
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
                        <a href="#creating-polls" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Creating Polls</a>
                        <a href="#how-voting-works" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">How Voting Works</a>
                        <a href="#viewing-results" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Viewing Results</a>
                        <a href="#closing-reopening" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Closing and Reopening</a>
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
                                Event polls let organizers add interactive multiple-choice questions to their events. Guests can vote by clicking on an option, and results are displayed immediately after voting. This is a great way to engage your audience, gather feedback, or let attendees weigh in on event decisions.
                            </p>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>Event Polls is a Pro feature. Upgrade your schedule to Pro to start creating polls on your events.</p>
                            </div>
                        </section>

                        <!-- Creating Polls -->
                        <section id="creating-polls" class="doc-section">
                            <h2 class="doc-heading">Creating Polls</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                To create a poll on an event:
                            </p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to the event edit page in your admin panel</li>
                                <li>Scroll down to the <strong>Polls</strong> section</li>
                                <li>Enter your question</li>
                                <li>Add between 2 and 10 options for voters to choose from</li>
                                <li>Save the event</li>
                            </ol>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                You can add up to 5 polls per event. Each poll has its own question and set of options.
                            </p>
                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Keep your poll questions clear and concise. Short, specific questions tend to get more engagement from your audience.</p>
                            </div>
                        </section>

                        <!-- How Voting Works -->
                        <section id="how-voting-works" class="doc-section">
                            <h2 class="doc-heading">How Voting Works</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Voting on polls is straightforward for your guests:
                            </p>
                            <ul class="doc-list mb-6">
                                <li><strong>Sign in required</strong> - Guests must be signed in to vote, which ensures each person can only vote once.</li>
                                <li><strong>One click to vote</strong> - Guests simply click on the option they want to vote for.</li>
                                <li><strong>One vote per poll</strong> - Each guest can cast one vote per poll. Votes cannot be changed after submission.</li>
                                <li><strong>Instant results</strong> - After voting, results are shown immediately so guests can see how others voted.</li>
                            </ul>
                        </section>

                        <!-- Viewing Results -->
                        <section id="viewing-results" class="doc-section">
                            <h2 class="doc-heading">Viewing Results</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                After a guest casts their vote, poll results are displayed with progress bars showing the count and percentage for each option. The leading option is highlighted so it is easy to see which choice is ahead.
                            </p>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                As an organizer, you can always see poll results in the event edit page of your admin panel, regardless of whether you have voted.
                            </p>
                        </section>

                        <!-- Closing and Reopening -->
                        <section id="closing-reopening" class="doc-section">
                            <h2 class="doc-heading">Closing and Reopening</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                You can control whether a poll is accepting votes by toggling it between active and closed states:
                            </p>
                            <ul class="doc-list mb-6">
                                <li><strong>Active polls</strong> - Guests can vote and results update in real time.</li>
                                <li><strong>Closed polls</strong> - Results are still visible, but no new votes can be cast.</li>
                            </ul>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                You can reopen a closed poll at any time if you want to allow voting again. Toggle the poll status from the event edit page in your admin panel.
                            </p>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events and manage event details</li>
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Sharing Your Schedule</a> - Share your schedule and grow your audience</li>
                            </ul>
                        </section>

                        @include('marketing.docs.partials.navigation')
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
