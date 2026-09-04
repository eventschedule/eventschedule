<x-docs-page
    key="sharing"
    description="Learn how to share your Event Schedule with the world. Embed on your website, share on social media, and grow your audience."
    lede="Reach your audience wherever they are. Embed your schedule on your website, share on social media, and let fans subscribe to your events."
>
    <x-slot:toc>
        <x-doc-nav-link href="#schedule-url">Your Schedule URL</x-doc-nav-link>
        <x-doc-nav-link href="#embed">Embedding on Your Website</x-doc-nav-link>
        <x-doc-nav-link href="#social">Social Media Sharing</x-doc-nav-link>
        <x-doc-nav-link href="#followers">Building Followers</x-doc-nav-link>
        <x-doc-nav-link href="#calendar-feeds">Calendar Subscriptions</x-doc-nav-link>
        <x-doc-nav-link href="#qr-code">QR Codes</x-doc-nav-link>
        <x-doc-nav-link href="#troubleshooting">Embed Troubleshooting</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Schedule URL -->
    <section id="schedule-url" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
            Your Schedule URL
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Every schedule gets a unique, shareable URL. This is the primary way people will find and view your events.</p>

        <x-doc-screenshot id="sharing--guest-portal" alt="Public schedule page" loading="eager" />

        <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Your schedule URL format:</p>
            <code class="doc-inline-code">{{ route('role.view_guest', ['subdomain' => 'your-schedule-name']) }}</code>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Share this link anywhere:</p>
        <ul class="doc-list">
            <li>Your website or bio</li>
            <li>Social media profiles</li>
            <li>Email signatures</li>
            <li>Printed materials</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Every event also has its own permanent URL underneath the schedule URL, and every sub-schedule has one too, so you can point people at one strand of your programme instead of the whole calendar.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Enterprise Feature: Custom Domain</div>
            <p>With an Enterprise plan, you can use your own domain (e.g. <code class="doc-inline-code">events.yourdomain.com</code>) for a more professional look. Configure this in <a href="{{ route('marketing.docs.creating_schedules') }}#custom-domain" class="doc-link">Schedule Settings</a>. Once a custom domain is live it becomes the canonical address, so it is the one your QR code and your feed URLs point at.</p>
        </div>
    </section>

    <!-- Embedding -->
    <section id="embed" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
            </svg>
            Embedding on Your Website
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Add your schedule directly to your website with an iframe. It loads live from Event Schedule, so your events update on your site without any extra work. Embedding the calendar is available on every plan, including Free.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">See our <a href="{{ marketing_url('/features/embed-calendar') }}" class="doc-link">embed calendar feature page</a> for a full overview and demo.</p>

        <h3 class="doc-subheading">Getting the Embed Code</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open your schedule in the admin portal</li>
            <li>Open the <strong class="text-gray-900 dark:text-white">Actions</strong> menu and choose <strong class="text-gray-900 dark:text-white">Embed Schedule</strong></li>
            <li>Pick a <strong class="text-gray-900 dark:text-white">Layout</strong>. Leave it on <strong class="text-gray-900 dark:text-white">Schedule default</strong> to follow your schedule's own Default Layout, or choose Calendar or List to pin this one frame</li>
            <li>Check the <strong class="text-gray-900 dark:text-white">Preview</strong>, which reloads each time you change the layout</li>
            <li>Copy the <strong class="text-gray-900 dark:text-white">Iframe Code</strong> with the button beside the field, or copy the <strong class="text-gray-900 dark:text-white">Embed URL</strong> if you would rather write the tag yourself</li>
            <li>Paste it into your website where you want the schedule to appear</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The layout picker only rewrites the code you copy. It never changes your schedule's own Default Layout setting.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Free Plans Get a Credit Line</div>
            <p>On a Free hosted plan the copied snippet includes a small "Powered by Event Schedule" line underneath the iframe. It sits outside the frame, so you can see exactly what you are pasting. <a href="{{ route('marketing.docs.schedule_styling') }}#remove-branding" class="doc-link">Removing branding</a> is a Pro feature. Selfhosted installs never add the line.</p>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Embed Tickets Too</div>
            <p>An individual event URL can also be embedded as a purchase or RSVP form. The RSVP form embed is free on every plan; the ticket purchase widget is a Pro feature. See the <a href="{{ route('marketing.docs.tickets') }}#embed-widget" class="doc-link">Embed Widget</a> section in the Selling Tickets guide.</p>
        </div>

        <h3 class="doc-subheading" id="embed-parameters">URL Parameters</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Add these to the embed URL to change how the frame renders. They go after <code class="doc-inline-code">?embed=true</code>, separated by <code class="doc-inline-code">&amp;</code>.</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">embed=true</code></td>
                        <td>Required. Renders the schedule on its own, with no header, footer or banner, and is the only URL another site is allowed to frame. <code class="doc-inline-code">embed=1</code> works too</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">layout=calendar</code><br><code class="doc-inline-code">layout=list</code></td>
                        <td>Force the month calendar or the list, whatever your schedule's Default Layout is set to. Leave it off and the embed follows that setting</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">schedule=slug</code></td>
                        <td>Show a single sub-schedule instead of everything. The sub-schedule's own page URL (your schedule URL followed by the slug) does the same thing and can be embedded directly</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">category=id</code></td>
                        <td>Show a single event category. The value is the category's numeric id, so the practical way to get it is to filter your schedule page and copy the id out of the resulting address bar</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">dark=true</code></td>
                        <td>Force dark mode. Left off, the frame uses the theme the visitor last chose on Event Schedule, and their system setting if they have never chosen one</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">lang=xx</code></td>
                        <td>Switch the frame to your schedule's second language, the one set by <strong class="text-gray-900 dark:text-white">Offer a second language to visitors</strong> under Details. Any other language code is dropped and the frame falls back to your schedule's own language</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">month=3</code> &amp; <code class="doc-inline-code">year=2027</code></td>
                        <td>Open the frame on a specific month instead of the current one. This moves the month calendar; the list always starts from today</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mt-4">The frame's width and height are plain iframe attributes, not parameters: <code class="doc-inline-code">width="100%"</code> lets it fill whatever column you drop it into, and you choose the height. Nothing measures the schedule and resizes the frame for you.</p>

        <h3 class="doc-subheading">Two Layouts on One Page</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Because the layout is set per URL, you can embed the same schedule twice on the same page and give each frame its own layout:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>One calendar, one list</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>&lt;iframe src="{{ route('role.view_guest', ['subdomain' => 'your-schedule-name']) }}?embed=true&#38;layout=calendar"
        width="100%" height="800" frameborder="0" style="border: none;"&gt;&lt;/iframe&gt;

&lt;iframe src="{{ route('role.view_guest', ['subdomain' => 'your-schedule-name']) }}?embed=true&#38;layout=list"
        width="100%" height="800" frameborder="0" style="border: none;"&gt;&lt;/iframe&gt;</code></pre>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Give the Calendar Room</div>
            <p>The month calendar needs about 768px of frame width to render as a grid. Below that it falls back to a day-by-day agenda, which looks much like the list. If you are putting two frames side by side in narrow columns, expect both to show the agenda.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Which Layout Wins</div>
            <p><code class="doc-inline-code">?layout=</code> always wins. Without it, an embed uses your schedule's <a href="{{ route('marketing.docs.schedule_styling') }}#event-layout" class="doc-link">Default Layout</a>. A visitor who switches between calendar and list on your own schedule page only changes it for themselves there, and never affects your embeds.</p>
        </div>

        <h3 class="doc-subheading">What Travels Into the Frame</h3>
        <ul class="doc-list">
            <li><strong class="text-gray-900 dark:text-white">Your events, live.</strong> The frame loads from your schedule on every page view, so a date you move this afternoon is already right on your site. You never re-paste the tag.</li>
            <li><strong class="text-gray-900 dark:text-white">Your look.</strong> Background, colour scheme and font come from your <a href="{{ route('marketing.docs.schedule_styling') }}" class="doc-link">Style</a> settings. Custom CSS, on Pro, applies inside the frame too.</li>
            <li><strong class="text-gray-900 dark:text-white">Nothing else.</strong> No header, no footer, no banner, no language switcher, which is why the language is set in the URL instead.</li>
            <li><strong class="text-gray-900 dark:text-white">No ads, ever.</strong> A Free schedule's own public pages can carry ads. An embed never does.</li>
            <li><strong class="text-gray-900 dark:text-white">No search-engine competition.</strong> The embed URL is served <code class="doc-inline-code">noindex, nofollow</code>, so the page that ranks is yours, not the frame inside it.</li>
        </ul>
    </section>

    <!-- Social Media -->
    <section id="social" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
            </svg>
            Social Media Sharing
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Share your schedule and individual events on social media to reach more people.</p>

        <h3 class="doc-subheading">Sharing Your Schedule</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Share your schedule URL on any platform. Event Schedule builds the preview card for you from what is already on the schedule:</p>
        <ul class="doc-list">
            <li><strong class="text-gray-900 dark:text-white">Title</strong> is your schedule name</li>
            <li><strong class="text-gray-900 dark:text-white">Description</strong> is the first 155 characters of your schedule description. If you have not written one, it is generated from your schedule name, short description and, for a venue, the town</li>
            <li><strong class="text-gray-900 dark:text-white">Image</strong> is your profile image, falling back to a generic Event Schedule image if you have not uploaded one</li>
        </ul>

        <h3 class="doc-subheading">Sharing Individual Events</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Each event has its own URL. Its preview card uses the event name as the title and the event flyer as the image. The description is the event's short description, or its full description, or a line built from the event name, the venue and the date.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Cards Are Cached by the Platform</div>
            <p>Social networks cache preview cards the first time a link is posted. If you change a flyer or a description after sharing, use that platform's own debugging or card-refresh tool to make it re-read the page.</p>
        </div>

        <h3 class="doc-subheading">Event Graphics</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Use the <a href="{{ route('marketing.docs.event_graphics') }}" class="doc-link">Event Graphics</a> feature to generate shareable images showing multiple upcoming events. Perfect for weekly social media posts. Open it from <strong class="text-gray-900 dark:text-white">Actions &rarr; Events Graphic</strong> in the admin portal. Generating graphics is a Pro feature.</p>
    </section>

    <!-- Followers -->
    <section id="followers" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </svg>
            Building Followers
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Following gives you a standing list of people who asked to hear from you. It is the audience your newsletters go to, and it builds up over time.</p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Following and Subscribing Are Different Lists</div>
            <p>Somebody who leaves an email address on your page and confirms it is a <strong class="text-gray-900 dark:text-white">subscriber</strong>, and subscribers are sent a digest automatically when you publish new events, batched and no more often than once every few days. Somebody signed in who presses Follow is an <strong class="text-gray-900 dark:text-white">account follower</strong>, and that list is reached only when you write and send a <a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">newsletter</a>. If someone wants your events on their own calendar with no email at all, point them at the <a href="#calendar-feeds" class="doc-link">iCal feed</a> instead.</p>
        </div>

        <h3 class="doc-subheading">How Following Works</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>A visitor clicks <strong class="text-gray-900 dark:text-white">Follow</strong> on your schedule</li>
            <li>A short consent panel tells them the schedule will be able to see their name and email. They can tick "don't ask again" so they are not asked on the next schedule they follow</li>
            <li>If they are not signed in, they are sent to sign up first. Following needs a free Event Schedule account, so an email address on its own is not enough</li>
            <li>Your schedule then appears on their <strong class="text-gray-900 dark:text-white">Following</strong> page, where they can also copy your iCal or RSS feed, or sync the schedule into their own Google Calendar</li>
            <li>They can unfollow at any time from that same page</li>
        </ol>

        <h3 class="doc-subheading">Managing Followers</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Open the <strong class="text-gray-900 dark:text-white">Followers</strong> tab in the admin portal. The tab label carries the running total, so you can see how many followers you have without opening it. Inside you get:</p>
        <ul class="doc-list">
            <li>A table of every follower: name, email, the schedule they followed and the date they followed it</li>
            <li>Sortable columns, newest first by default, and paging once the list grows</li>
            <li>A <strong class="text-gray-900 dark:text-white">QR Code</strong> button, covered in <a href="#qr-code" class="doc-link">QR Codes</a> below</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Send <a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">newsletters</a> to your followers to keep them engaged and promote upcoming events. Newsletters are available on every plan, and the monthly allowance counts recipients rather than sends: a newsletter to 100 followers uses 100 of the allowance.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Privacy</div>
            <p>When users follow your schedule, you can see their name and email address in the followers tab so you can keep them informed. Their email is shared only with you, the schedule owner, and is never sold or shared with third parties.</p>
        </div>
    </section>

    <!-- Calendar Subscriptions -->
    <section id="calendar-feeds" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 19.5v-.75a7.5 7.5 0 00-7.5-7.5H4.5m0-6.75h.75c7.87 0 14.25 6.38 14.25 14.25v.75M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
            </svg>
            Calendar Subscriptions
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Let your audience subscribe to your events directly in their calendar apps or feed readers. Both feeds are public, need no account, and are available on every plan.</p>

        <h3 class="doc-subheading">Finding Your Feed URLs</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">In the admin portal, edit your schedule and open <strong class="text-gray-900 dark:text-white">Integrations &rarr; Advanced</strong>. The <strong class="text-gray-900 dark:text-white">iCal Feed</strong> and <strong class="text-gray-900 dark:text-white">RSS Feed</strong> fields each have a copy button. Your followers can copy the same two URLs from their own <strong class="text-gray-900 dark:text-white">Following</strong> page.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">iCal Feed</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Works with Google Calendar, Apple Calendar, Outlook, and any calendar app that supports subscribing to a URL.</p>
                <p class="text-sm"><code class="doc-inline-code">{{ route('feed.ical', ['subdomain' => 'your-schedule-name']) }}</code></p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">RSS Feed</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">For readers and aggregators that support RSS. Capped at 50 items, and a repeating event contributes only its next occurrence.</p>
                <p class="text-sm"><code class="doc-inline-code">{{ route('feed.rss', ['subdomain' => 'your-schedule-name']) }}</code></p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Add to Calendar Buttons</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Each event page has an "Add to Calendar" button offering Google Calendar, Apple Calendar (which downloads an .ics file) and Microsoft Outlook. This copies one event across once; it is not a subscription.</p>
            </div>
        </div>

        <h3 class="doc-subheading">What the Feeds Contain</h3>
        <ul class="doc-list">
            <li>Upcoming events only. An event that runs 24 hours or longer stays in the feed until it has actually ended</li>
            <li>Repeating events are expanded into individual dated entries for the next 90 days, so a weekly night shows up as a run of entries rather than one</li>
            <li>Drafts, unlisted events, cancelled events and password-protected events are all left out</li>
            <li>Each entry carries the event title, description, venue address and a link back to the event page</li>
            <li>Start and end times are exported as absolute instants, so every subscriber's calendar app shows them in that subscriber's own local time</li>
        </ul>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Refresh Timing</div>
            <p>Subscribed calendars pick up your changes on their own, with no action from your subscribers. The feed itself is cached for an hour, and calendar apps then refresh on their own schedule, which for Google Calendar can be several hours. Do not expect an edit to reach a subscriber's calendar within a minute or two.</p>
        </div>
    </section>

    <!-- QR Codes -->
    <section id="qr-code" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
            </svg>
            QR Codes
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Generate a QR code for your schedule to use in printed materials, posters, or at your venue. It is available on every plan, including Free.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open the <strong class="text-gray-900 dark:text-white">Followers</strong> tab in the admin portal</li>
            <li>Click <strong class="text-gray-900 dark:text-white">QR Code</strong>. The image downloads straight away as <code class="doc-inline-code">qr-code.png</code>, a PNG about 300 pixels square with a quiet margin already around it</li>
            <li>Use it on flyers, posters, table tents, or anywhere else</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">When scanned, the QR code takes people directly to your schedule where they can view events and follow you. If you have an Enterprise custom domain set up, the code points at that domain rather than the eventschedule.com address.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">A Different QR Code from Ticket Check-In</div>
            <p>This code points at your schedule and is for the public. Tickets carry their own QR codes, on every plan, including the 25 paid tickets a month a Free schedule sells, and the door scanner that admits people is free too. Only the live check-in dashboard, with its running count and per-ticket-type breakdown, is a Pro feature. See <a href="{{ route('marketing.docs.tickets') }}#check-in" class="doc-link">Check-In</a> in the Selling Tickets guide.</p>
        </div>
    </section>

    <!-- Embed Troubleshooting -->
    <section id="troubleshooting" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Embed Troubleshooting
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Common issues when embedding your schedule and how to fix them.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">The frame is empty or the browser refuses to load it</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Almost always a missing <code class="doc-inline-code">?embed=true</code>. Every other Event Schedule URL is served with framing switched off, so a plain schedule link inside an iframe is refused by the browser. Copy the Embed URL out of the Embed Schedule dialog rather than out of your address bar. Privacy extensions that block third-party frames are the other, rarer cause; test in a private window.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Embed appears too small or cut off</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The embed iframe needs explicit height. Set a minimum height of 600px for comfortable viewing. Example: <code class="doc-inline-code">height="800"</code> or <code class="doc-inline-code">style="height: 800px;"</code></p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Scrollbars appear on the embed</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Increase the height of your iframe. The content may be taller than the container. For schedules with many events, try <code class="doc-inline-code">height="1000"</code> or higher.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Embed doesn't resize on mobile</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Set the width to 100% and wrap the iframe in a responsive container. Example: <code class="doc-inline-code">width="100%"</code> and put it inside a <code class="doc-inline-code">&lt;div style="max-width: 100%;"&gt;</code></p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Embed shows the wrong layout</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Add <code class="doc-inline-code">&amp;layout=calendar</code> or <code class="doc-inline-code">&amp;layout=list</code> to the embed URL to pin it, instead of relying on your schedule's Default Layout. Remember that a frame narrower than about 768px shows the agenda even when the calendar is pinned.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Embed shows the wrong theme</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Add <code class="doc-inline-code">&amp;dark=true</code> to the embed URL to force dark mode. See <a href="#embed-parameters" class="doc-link">URL Parameters</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Embed shows the wrong language</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400"><code class="doc-inline-code">&amp;lang=</code> only accepts your schedule's second language. Any other code is dropped and the frame falls back to your schedule's own language. Turn on <strong class="text-gray-900 dark:text-white">Offer a second language to visitors</strong> under Details first.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">An event is missing from the embed</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The frame shows the same events as your public schedule page, so a draft, an unlisted event, or one still awaiting your acceptance will not appear. Check the event on your schedule page first; if it is missing there too, the problem is the event, not the embed.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Responsive Embed Code</div>
            <pre class="text-xs text-gray-600 dark:text-gray-300 mt-2 overflow-x-auto"><code>&lt;div style="position: relative; padding-bottom: 75%; height: 0; overflow: hidden;"&gt;
&lt;iframe src="{{ route('role.view_guest', ['subdomain' => 'your-schedule-name']) }}?embed=true"
style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
frameborder="0"&gt;&lt;/iframe&gt;
&lt;/div&gt;</code></pre>
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
            <li><a href="{{ route('marketing.docs.event_graphics') }}" class="doc-link">Event Graphics</a> - Generate shareable images for social media</li>
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - Add events to your schedule</li>
            <li><a href="{{ route('marketing.docs.schedule_styling') }}" class="doc-link">Schedule Styling</a> - Customize your schedule's look before sharing</li>
            <li><a href="{{ route('marketing.docs.analytics') }}" class="doc-link">Analytics</a> - Track how sharing drives views and engagement</li>
            <li><a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">Newsletters</a> - Send newsletters to engage your followers</li>
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - Sell tickets for your events</li>
            <li><a href="{{ route('marketing.docs.tickets') }}#embed-widget" class="doc-link">Embed Widget</a> - Embed a ticket form on your website</li>
        </ul>
    </section>


    <x-slot:schema>
        <script type="application/ld+json" {!! nonce_attr() !!}>
        {
            "@context": "https://schema.org",
            "@type": "HowTo",
            "name": "How to Share Your Event Schedule",
            "description": "Learn how to share your schedule with the world. Embed on your website, share on social media, and grow your audience.",
            "totalTime": "PT5M",
            "step": [
                {
                    "@type": "HowToStep",
                    "name": "Share Your Schedule URL",
                    "text": "Every schedule gets a unique, shareable URL. Share this link on your website, social media profiles, email signatures, or printed materials.",
                    "url": "{{ url(route('marketing.docs.sharing')) }}#schedule-url"
                },
                {
                    "@type": "HowToStep",
                    "name": "Embed on Your Website",
                    "text": "Open your schedule in the admin portal, choose Embed Schedule from the Actions menu, pick a layout, copy the iframe code, and paste it into your website.",
                    "url": "{{ url(route('marketing.docs.sharing')) }}#embed"
                },
                {
                    "@type": "HowToStep",
                    "name": "Share on Social Media",
                    "text": "Share your schedule or individual event URLs on social media. Event Schedule automatically generates preview cards.",
                    "url": "{{ url(route('marketing.docs.sharing')) }}#social"
                },
                {
                    "@type": "HowToStep",
                    "name": "Build Your Followers",
                    "text": "Visitors with a free Event Schedule account can follow your schedule, which adds it to their Following page and adds them to the audience your newsletters go to. View and manage followers from the Followers tab in the admin portal.",
                    "url": "{{ url(route('marketing.docs.sharing')) }}#followers"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
