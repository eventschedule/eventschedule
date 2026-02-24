<x-marketing-layout>
    <x-slot name="title">Availability Calendar Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Availability Calendar</x-slot>
    <x-slot name="description">Learn how to mark your available and unavailable dates using Event Schedule's availability calendar feature.</x-slot>
    <x-slot name="socialImage">social/docs.png</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Availability Calendar Documentation - Event Schedule",
        "description": "Learn how to mark your available and unavailable dates using Event Schedule's availability calendar feature.",
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
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-orange-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Availability Calendar" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Availability Calendar</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Mark your available and unavailable dates so team members and collaborators know when you can be booked.
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
                        <a href="#setting-availability" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Setting Availability</a>
                        <a href="#team-view" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">How Team Members See It</a>
                        <a href="#public-view" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Public View</a>
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
                                The Availability Calendar lets you mark specific dates as unavailable on your schedule. This is useful for:
                            </p>

                            <x-doc-screenshot id="availability--calendar" alt="Availability calendar" loading="eager" />

                            <ul class="doc-list mb-6">
                                <li><strong>Talent</strong> (musicians, DJs, performers) - Show venues and promoters which dates you are free for bookings</li>
                                <li><strong>Venues</strong> (bars, clubs, theaters) - Indicate which dates are open for booking by performers or event organizers</li>
                                <li><strong>Teams</strong> - Coordinate availability across multiple team members working on the same schedule</li>
                            </ul>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>The availability calendar is available on Talent and Venue schedule types. It appears as a tab in your schedule's admin panel.</p>
                            </div>
                        </section>

                        <!-- Setting Availability -->
                        <section id="setting-availability" class="doc-section">
                            <h2 class="doc-heading">Setting Availability</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                To mark dates as unavailable:
                            </p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to your schedule's admin panel and click the <strong>Availability</strong> tab</li>
                                <li>Click on any date in the calendar to toggle it as unavailable. Your unavailable dates appear with a red overlay and an "Unavailable" label.</li>
                                <li>Click <strong>Save</strong> to confirm your changes</li>
                            </ol>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                You can click the same date again to remove the unavailable marker and make yourself available on that date.
                            </p>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Use the calendar navigation arrows to move between months and mark dates further in advance.</p>
                            </div>
                        </section>

                        <!-- How Team Members See It -->
                        <section id="team-view" class="doc-section">
                            <h2 class="doc-heading">How Team Members See It</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                When multiple team members use the availability calendar, other team members' unavailable dates are visible on the <strong>Schedule tab</strong> (not the Availability tab):
                            </p>

                            <ul class="doc-list mb-6">
                                <li>Dates where team members are unavailable are highlighted in <strong>orange</strong> on the Schedule tab calendar</li>
                                <li>Hovering over the info icon in the corner of an orange date shows a <strong>tooltip</strong> listing the names of team members who are unavailable on that date</li>
                                <li>Each team member can only edit their own availability on the Availability tab; they cannot change another member's dates</li>
                            </ul>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                This makes it easy to coordinate scheduling and find dates when everyone is available.
                            </p>
                        </section>

                        <!-- Public View -->
                        <section id="public-view" class="doc-section">
                            <h2 class="doc-heading">Public View</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Availability information is <strong>not shown publicly</strong> on your schedule's guest-facing page. Visitors to your schedule will only see your events.
                            </p>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The availability calendar is an internal tool for you and your team. It is only visible to logged-in users who are owners or team members of the schedule.
                            </p>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>If you want to publicly show which dates are open for booking, consider adding that information to your schedule's description or creating events for available dates.</p>
                            </div>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.schedule_basics') }}" class="text-cyan-400 hover:text-cyan-300">Schedule Setup</a> - Configure your schedule type and settings</li>
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events to your schedule</li>
                                <li><a href="{{ route('marketing.docs.creating_schedules') }}" class="text-cyan-400 hover:text-cyan-300">Advanced Schedule Settings</a> - Team members and collaboration features</li>
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
        "name": "How to Set Your Availability in Event Schedule",
        "description": "Learn how to mark your available and unavailable dates using Event Schedule's availability calendar feature.",
        "totalTime": "PT3M",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Open the Availability Tab",
                "text": "Go to your schedule's admin panel and click the Availability tab to access the availability calendar.",
                "url": "{{ url(route('marketing.docs.availability')) }}#setting-availability"
            },
            {
                "@type": "HowToStep",
                "name": "Mark Unavailable Dates",
                "text": "Click on any date in the calendar to toggle it as unavailable. Unavailable dates appear with a red overlay.",
                "url": "{{ url(route('marketing.docs.availability')) }}#setting-availability"
            },
            {
                "@type": "HowToStep",
                "name": "Save Your Changes",
                "text": "Click Save to confirm your availability changes. You can click dates again to remove unavailable markers.",
                "url": "{{ url(route('marketing.docs.availability')) }}#setting-availability"
            }
        ]
    }
    </script>
</x-marketing-layout>
