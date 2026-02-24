<x-marketing-layout>
    <x-slot name="title">Fan Content Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Fan Content</x-slot>
    <x-slot name="description">Learn how to let fans submit videos and comments on your events in Event Schedule.</x-slot>
    <x-slot name="socialImage">social/docs.png</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Fan Content Documentation - Event Schedule",
        "description": "Learn how to let fans submit videos and comments on your events in Event Schedule.",
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
            <x-docs-breadcrumb currentTitle="Fan Content" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Fan Content</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Let fans submit YouTube videos and comments on your events. All submissions require organizer approval before appearing publicly.
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
                        <a href="#enabling" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Enabling Fan Content</a>
                        <a href="#fan-videos" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Fan Videos</a>
                        <a href="#fan-comments" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Fan Comments</a>
                        <a href="#moderation" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Moderation</a>
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
                                Fan Content lets your audience submit YouTube or Vimeo videos and text comments on event pages. This is available for Curator-type schedules and gives fans a way to share their experience while keeping you in control of what appears publicly.
                            </p>

                            <x-doc-screenshot id="fan-content--videos-tab" alt="Fan videos management tab" loading="eager" />

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                All fan submissions go through an approval workflow. Nothing appears on your public event page until you approve it.
                            </p>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>Fan Content is available for schedules with the Curator type. You can change your schedule type in the schedule settings.</p>
                            </div>
                        </section>

                        <!-- Enabling Fan Content -->
                        <section id="enabling" class="doc-section">
                            <h2 class="doc-heading">Enabling Fan Content</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                To enable fan content on your schedule:
                            </p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to your schedule's admin panel</li>
                                <li>Open <strong>Settings</strong></li>
                                <li>Set the schedule type to <strong>Curator</strong></li>
                                <li>Save your changes</li>
                            </ol>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Once the schedule type is set to Curator, the <strong>Videos</strong> tab appears in your admin panel and fans can submit content on your public event pages.
                            </p>
                        </section>

                        <!-- Fan Videos -->
                        <section id="fan-videos" class="doc-section">
                            <h2 class="doc-heading">Fan Videos</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Fans can submit YouTube or Vimeo video links on your public event pages. Videos can be attached to the event itself or to specific event parts (e.g., individual performances or sessions).
                            </p>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                When a fan submits a video:
                            </p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>They paste a YouTube or Vimeo URL on the event page</li>
                                <li>They select which event or event part the video belongs to</li>
                                <li>The submission is sent to you for review</li>
                                <li>Once approved, the video appears embedded on the public event page</li>
                            </ol>
                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Fan videos are a great way to build a library of event recordings contributed by your audience, especially for live music, conferences, and performances.</p>
                            </div>
                        </section>

                        <!-- Fan Comments -->
                        <section id="fan-comments" class="doc-section">
                            <h2 class="doc-heading">Fan Comments</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Fans can also leave text comments on events and event parts. Comments follow the same approval workflow as videos.
                            </p>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                When a fan submits a comment:
                            </p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>They write their comment on the event page</li>
                                <li>The comment is sent to you for review</li>
                                <li>Once approved, the comment appears on the public event page</li>
                            </ol>
                        </section>

                        <!-- Moderation -->
                        <section id="moderation" class="doc-section">
                            <h2 class="doc-heading">Moderation</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                You have full control over which fan content appears on your event pages. There are two ways to moderate submissions:
                            </p>
                            <ul class="doc-list mb-6">
                                <li><strong>Admin panel</strong> - Go to the Videos tab in your admin panel to see all pending submissions. You can approve or reject each one individually.</li>
                                <li><strong>Email notifications</strong> - When a fan submits content, you receive an email notification with links to approve or reject the submission directly.</li>
                            </ul>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Approved content appears on the public event page immediately. Rejected submissions are removed and the fan is not notified.
                            </p>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>You can remove previously approved content at any time from the Videos tab in your admin panel.</p>
                            </div>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events and manage event parts</li>
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
