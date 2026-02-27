<x-marketing-layout>
    <x-slot name="title">Managing Schedules - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Managing Schedules</x-slot>
    <x-slot name="description">Learn how to manage your schedule in Event Schedule. View events, approve fan videos, set availability, handle requests, manage your team, and more.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Managing Schedules - Event Schedule",
        "description": "Learn how to manage your schedule in Event Schedule. View events, approve fan videos, set availability, handle requests, manage your team, and more.",
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
        "dateModified": "2026-02-27"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Managing Schedules" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Managing Schedules</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                View and manage your events, approve fan content, set availability, handle requests, and manage your team from the admin panel.
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
                        <a href="#schedule" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Schedule</a>
                        <a href="#videos" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Videos</a>
                        <a href="#availability" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Availability</a>
                        <a href="#requests" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Requests</a>
                        <a href="#profile" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Profile</a>
                        <a href="#followers" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Followers</a>
                        <a href="#team" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Team</a>
                        <a href="#plan" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Plan</a>
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
                                Your schedule's admin panel is the central hub for day-to-day management. It contains several tabs, each focused on a different aspect of running your schedule.
                            </p>

                            <x-doc-screenshot id="managing-schedules--schedule-tab" alt="Schedule admin panel with tabs" loading="eager" />

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The tabs you see depend on your schedule type and plan. The sections below cover each tab in detail.
                            </p>
                        </section>

                        <!-- Schedule -->
                        <section id="schedule" class="doc-section">
                            <h2 class="doc-heading">Schedule</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The <strong class="text-gray-900 dark:text-white">Schedule</strong> tab is the main view of your admin panel. It shows your events in a calendar layout, organized by month.
                            </p>

                            <ul class="doc-list mb-6">
                                <li>Use the <strong>arrow buttons</strong> to navigate between months</li>
                                <li>Click any <strong>event</strong> to open it for editing</li>
                                <li>Click <strong>"Add Event"</strong> to create a new event manually</li>
                                <li>Events without a date appear in the <strong>Unscheduled</strong> section below the calendar</li>
                            </ul>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                For details on adding and editing events, see <x-link href="{{ route('marketing.docs.creating_events') }}">Creating Events</x-link>.
                            </p>
                        </section>

                        <!-- Videos -->
                        <section id="videos" class="doc-section">
                            <h2 class="doc-heading">Videos</h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400 mb-4">Curator schedules only</span>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The <strong class="text-gray-900 dark:text-white">Videos</strong> tab lets you review and manage fan-submitted video content. Videos are organized by talent, making it easy to find and curate submissions.
                            </p>

                            <x-doc-screenshot id="managing-schedules--videos-tab" alt="Videos tab showing fan submissions" />

                            <ul class="doc-list mb-6">
                                <li><strong>Approve</strong> or <strong>reject</strong> individual video submissions</li>
                                <li>Videos are grouped by the talent they were submitted for</li>
                                <li>Approved videos appear on the public event page</li>
                            </ul>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                To configure fan content settings for your events, see <x-link href="{{ route('marketing.docs.creating_events') }}#fan-content">Creating Events: Fan Content</x-link>.
                            </p>
                        </section>

                        <!-- Availability -->
                        <section id="availability" class="doc-section">
                            <h2 class="doc-heading">Availability</h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 mb-2">Talent schedules only</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 mb-4">Enterprise</span>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The <strong class="text-gray-900 dark:text-white">Availability</strong> tab lets you mark specific dates as unavailable on your schedule. This is useful for showing venues and promoters which dates you are free for bookings.
                            </p>

                            <x-doc-screenshot id="managing-schedules--availability" alt="Availability calendar" />

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">Setting Availability</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Click on any <strong>date</strong> in the calendar to toggle it as unavailable. Unavailable dates appear with a red overlay and an "Unavailable" label.</li>
                                <li>Click the same date again to <strong>remove</strong> the unavailable marker</li>
                                <li>Click <strong>Save</strong> to confirm your changes</li>
                            </ol>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Use the calendar navigation arrows to move between months and mark dates further in advance.</p>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">How Team Members See It</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                When multiple team members use the availability calendar, other members' unavailable dates are visible on the <strong>Schedule tab</strong>:
                            </p>
                            <ul class="doc-list mb-6">
                                <li>Dates where team members are unavailable are highlighted in <strong>orange</strong> on the Schedule tab calendar</li>
                                <li>Hovering over the info icon on an orange date shows a <strong>tooltip</strong> listing which team members are unavailable</li>
                                <li>Each team member can only edit their <strong>own</strong> availability</li>
                            </ul>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>Availability information is <strong>not shown publicly</strong>. It is only visible to logged-in team members of the schedule.</p>
                            </div>
                        </section>

                        <!-- Requests -->
                        <section id="requests" class="doc-section">
                            <h2 class="doc-heading">Requests</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The <strong class="text-gray-900 dark:text-white">Requests</strong> tab appears when your schedule has pending event requests. Anyone with the public sign-up link can submit a request to add an event to your schedule.
                            </p>

                            <x-doc-screenshot id="managing-schedules--requests-tab" alt="Requests tab showing pending requests" />

                            <ul class="doc-list mb-6">
                                <li>Review each request and <strong>accept</strong> or <strong>reject</strong> it individually</li>
                                <li>Use <strong>"Accept All"</strong> or <strong>"Reject All"</strong> to handle requests in bulk</li>
                                <li>Accepted requests become events on your schedule</li>
                            </ul>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                To configure the public sign-up link and request settings, see <x-link href="{{ route('marketing.docs.creating_schedules') }}#settings-requests">Creating Schedules: Requests</x-link>.
                            </p>
                        </section>

                        <!-- Profile -->
                        <section id="profile" class="doc-section">
                            <h2 class="doc-heading">Profile</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The <strong class="text-gray-900 dark:text-white">Profile</strong> tab shows a read-only preview of your schedule's public profile, including:
                            </p>

                            <x-doc-screenshot id="managing-schedules--profile-tab" alt="Profile tab showing schedule info" />

                            <ul class="doc-list mb-6">
                                <li><strong>Description</strong> - Your schedule's markdown-formatted description</li>
                                <li><strong>YouTube videos</strong> - Any linked YouTube videos</li>
                                <li><strong>Social links</strong> - Connected social media profiles</li>
                                <li><strong>Google Map</strong> - Displayed if an address is configured</li>
                            </ul>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                To edit these details, go to <x-link href="{{ route('marketing.docs.creating_schedules') }}#details">Edit Schedule</x-link>.
                            </p>
                        </section>

                        <!-- Followers -->
                        <section id="followers" class="doc-section">
                            <h2 class="doc-heading">Followers</h2>
                            @if(config('app.hosted'))
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The <strong class="text-gray-900 dark:text-white">Followers</strong> tab shows a list of users who follow your schedule. Followers receive notifications when you publish new events.
                            </p>

                            <ul class="doc-list mb-6">
                                <li>View a list of all your <strong>current followers</strong></li>
                                <li>Share your schedule's <strong>follow link</strong> to grow your audience</li>
                            </ul>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                For more on sharing and growing your audience, see <x-link href="{{ route('marketing.docs.sharing') }}#followers">Sharing: Followers</x-link>.
                            </p>
                            @else
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The <strong class="text-gray-900 dark:text-white">Followers</strong> tab is available on the hosted version of Event Schedule (eventschedule.com). It shows a list of users who follow your schedule and lets you share a follow link to grow your audience.
                            </p>
                            @endif
                        </section>

                        <!-- Team -->
                        <section id="team" class="doc-section">
                            <h2 class="doc-heading">Team</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The <strong class="text-gray-900 dark:text-white">Team</strong> tab lets you manage who has access to your schedule. You can invite team members and assign them different permission levels.
                            </p>

                            <x-doc-screenshot id="managing-schedules--team-tab" alt="Team management tab" />

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">Member Roles</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Role</th>
                                            <th>Permissions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Owner</strong></td>
                                            <td>Full access, including billing and schedule deletion</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Admin</strong></td>
                                            <td>Can manage events, settings, and team members</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Viewer</strong></td>
                                            <td>Read-only access to the admin panel</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">Managing Members</h3>
                            <ul class="doc-list mb-6">
                                <li>Click <strong>"Add Member"</strong> to invite someone by email</li>
                                <li><strong>Resend</strong> an invitation if the recipient hasn't accepted yet</li>
                                <li><strong>Remove</strong> a member to revoke their access</li>
                            </ul>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>On the Free plan, schedules can have one member (the owner). The Pro plan supports up to two members, and the Enterprise plan supports unlimited team members.</p>
                            </div>
                        </section>

                        <!-- Plan -->
                        <section id="plan" class="doc-section">
                            <h2 class="doc-heading">Plan</h2>
                            @if(config('app.hosted'))
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The <strong class="text-gray-900 dark:text-white">Plan</strong> tab shows your schedule's current subscription plan and lets you manage it.
                            </p>

                            <ul class="doc-list mb-6">
                                <li>View your <strong>current plan</strong> (Free, Pro, or Enterprise)</li>
                                <li>See your <strong>trial status</strong> and remaining trial days</li>
                                <li><strong>Upgrade</strong> or change your plan</li>
                            </ul>
                            @else
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The <strong class="text-gray-900 dark:text-white">Plan</strong> tab is available on the hosted version of Event Schedule (eventschedule.com). It shows your schedule's current subscription plan and lets you upgrade.
                            </p>
                            @endif
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><x-link href="{{ route('marketing.docs.creating_schedules') }}">Creating Schedules</x-link> - Set up and configure your schedule</li>
                                <li><x-link href="{{ route('marketing.docs.creating_events') }}">Creating Events</x-link> - Add and edit events on your schedule</li>
                                <li><x-link href="{{ route('marketing.docs.sharing') }}">Sharing Your Schedule</x-link> - Share your schedule and grow your audience</li>
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
