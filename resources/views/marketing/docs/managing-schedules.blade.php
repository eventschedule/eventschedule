<x-marketing-layout>
    <x-slot name="title">Managing Schedules - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Managing Schedules</x-slot>
    <x-slot name="description">Learn how to manage your schedule in Event Schedule. View events, assign videos, set availability, handle requests, manage your team, and more.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Managing Schedules - Event Schedule",
        "description": "Learn how to manage your schedule in Event Schedule. View events, assign videos, set availability, handle requests, manage your team, and more.",
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
                View and manage your events, assign videos, set availability, handle requests, and manage your team from the admin panel.
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                Schedule
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                                Videos
                            </h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400 mb-4">Curator schedules only</span>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The <strong class="text-gray-900 dark:text-white">Videos</strong> tab lets you find and assign YouTube videos to your talent. Videos are searched automatically by talent name, and you can select the best match for each.
                            </p>

                            <x-doc-screenshot id="managing-schedules--videos-tab" alt="Videos tab showing YouTube search results" />

                            <ul class="doc-list mb-6">
                                <li><strong>Search YouTube</strong> for videos matching each talent's name</li>
                                <li>View video details including <strong>title</strong>, <strong>channel</strong>, <strong>views</strong>, and <strong>likes</strong></li>
                                <li><strong>Select</strong> a video to display on the talent's public profile</li>
                                <li><strong>Skip</strong> talents that don't need a video</li>
                            </ul>
                        </section>

                        <!-- Availability -->
                        <section id="availability" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Availability
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                                </svg>
                                Requests
                            </h2>
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
                                To configure the public sign-up link and request settings, see <x-link href="{{ route('marketing.docs.creating_schedules') }}#engagement-requests">Creating Schedules: Requests</x-link>.
                            </p>
                        </section>


                        <!-- Followers -->
                        <section id="followers" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                Followers
                            </h2>
                            @if(config('app.hosted'))
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The <strong class="text-gray-900 dark:text-white">Followers</strong> tab shows a list of users who follow your schedule. Followers receive notifications when you publish new events.
                            </p>

                            <ul class="doc-list mb-6">
                                <li>View a list of all your <strong>current followers</strong></li>
                                <li>Share your schedule's <strong>follow link</strong> to grow your audience</li>
                                <li>Generate a <strong>QR code</strong> for your follow link to share in print or at events</li>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                                Team
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                </svg>
                                Plan
                            </h2>
                            @if(config('app.hosted'))
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The <strong class="text-gray-900 dark:text-white">Plan</strong> tab shows your schedule's current subscription plan and lets you manage it.
                            </p>

                            <ul class="doc-list mb-6">
                                <li>View your <strong>current plan</strong> (Free, Pro, or Enterprise) and <strong>subscription status</strong> (Active, Trial, Cancelled, Past Due)</li>
                                <li>See your <strong>trial status</strong> and remaining trial days</li>
                                <li>View your <strong>payment method</strong> on file</li>
                                <li>Track <strong>newsletter email usage</strong> with a progress bar showing how many newsletter emails you've sent this month relative to your plan limit</li>
                                <li><strong>Upgrade</strong> to Pro or Enterprise</li>
                                <li><strong>Manage subscription</strong> through the Stripe portal, switch between <strong>monthly and yearly</strong> billing, or <strong>cancel</strong> your subscription</li>
                                <li><strong>Resume</strong> your subscription during a cancellation grace period</li>
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
