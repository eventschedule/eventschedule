<x-docs-page
    key="selfhost/google-calendar"
    title="Google Calendar Sync Setup for Selfhost - Event Schedule"
    description="Set up two-way Google Calendar sync on a selfhosted Event Schedule install: OAuth credentials, the webhook secret, the scheduler cron and the queue."
    lede="Set up and use the Google Calendar integration for two-way sync between Event Schedule and Google Calendar."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#prerequisites">Prerequisites</x-doc-nav-link>
        <x-doc-nav-group label="Setup Instructions" href="#setup">
            <x-doc-nav-link href="#setup-console">1. Google Cloud project</x-doc-nav-link>
            <x-doc-nav-link href="#setup-consent">2. Consent screen and scopes</x-doc-nav-link>
            <x-doc-nav-link href="#setup-credentials">3. OAuth credentials</x-doc-nav-link>
            <x-doc-nav-link href="#setup-env">4. Environment</x-doc-nav-link>
            <x-doc-nav-link href="#setup-cron">5. Scheduler cron</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Features" href="#features">
            <x-doc-nav-link href="#sync-direction">Sync direction</x-doc-nav-link>
            <x-doc-nav-link href="#what-is-synced">What is synced</x-doc-nav-link>
            <x-doc-nav-link href="#inbound-events">Events from Google</x-doc-nav-link>
            <x-doc-nav-link href="#realtime">Real-time and polling</x-doc-nav-link>
            <x-doc-nav-link href="#delete-sync">Deletions in Google</x-doc-nav-link>
            <x-doc-nav-link href="#calendar-sync-tab">An event's Calendar sync tab</x-doc-nav-link>
            <x-doc-nav-link href="#member-sync">Member calendars</x-doc-nav-link>
            <x-doc-nav-link href="#read-only">Read-only connections</x-doc-nav-link>
            <x-doc-nav-link href="#one-time-import">One-time import</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#usage">Usage</x-doc-nav-link>
        <x-doc-nav-link href="#api-endpoints">API Endpoints</x-doc-nav-link>
        <x-doc-nav-link href="#troubleshooting">Troubleshooting</x-doc-nav-link>
        <x-doc-nav-link href="#security">Security Considerations</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            Overview
        </h2>
        <p>This page is for whoever runs the install. Google Calendar sync needs one OAuth client in Google Cloud and four environment variables. Once they are set, every user can connect their own Google account, and each schedule's owner decides which calendar the schedule syncs with and in which direction. Nothing is configured per schedule on the server.</p>

        <p>The OAuth client you create here is used in three places:</p>
        <ul class="doc-list">
            <li><strong>Calendar sync</strong>, the subject of this page: a schedule and one Google calendar kept in step, in one direction or both</li>
            <li><strong>Import from Google Calendar</strong>: a one-time, read-only copy of the events a schedule's owner picks on the import page. See <a href="#one-time-import" class="doc-link">One-time import</a></li>
            <li><strong>Log in with Google</strong>: the button appears on the log in page as soon as <code class="doc-inline-code">GOOGLE_CLIENT_ID</code> is set, so register its redirect URIs too, in <a href="#setup-credentials" class="doc-link">step 3</a></li>
        </ul>

        <h3 id="where-it-lives" class="doc-subheading">Where It Lives in the App</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Screen</th>
                        <th>What is done there, and by whom</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Settings</strong> &rarr; <strong>Integrations</strong> &rarr; <strong>Google Settings</strong></td>
                        <td>Every user connects their own Google account here with <strong>Connect Google Calendar</strong>, and later disconnects it</td>
                    </tr>
                    <tr>
                        <td><strong>Edit Schedule</strong> &rarr; <strong>Integrations</strong> &rarr; <strong>Google Calendar</strong> row</td>
                        <td>The schedule's owner picks the calendar, the sync direction and what a deletion in Google does here, and can run <strong>Resync to Google Calendar</strong>. Other team members get <strong>Sync to My Calendar</strong> in the same row</td>
                    </tr>
                    <tr>
                        <td>Event form &rarr; <strong>Calendar sync</strong> tab</td>
                        <td>Shows whether this event has a copy on the calendar. <strong>Sync Now</strong> and <strong>Remove</strong> are the owner's to use</td>
                    </tr>
                    <tr>
                        <td>A schedule's page &rarr; <strong>Actions</strong> &rarr; <strong>Sync Events</strong></td>
                        <td>The owner runs the schedule's saved direction now</td>
                    </tr>
                    <tr>
                        <td><strong>Actions</strong> &rarr; <strong>Import Events</strong> &rarr; <strong>Google Calendar</strong></td>
                        <td>The owner's one-time, read-only import</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <x-doc-screenshot id="creating-schedules--section-integrations" alt="The Integrations tab of the schedule form, with Google Calendar, Outlook Calendar, CalDAV Calendar and Calendar text and feeds as rows that open in place" />

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Included on every plan</div>
            <p>Google Calendar sync is a free feature, and a selfhosted install resolves to the Enterprise tier, so nothing on this page is held back by a plan. It does need the environment variables below: without them the <strong>Connect Google Calendar</strong> button has no credentials to use.</p>
        </div>
    </section>

    <!-- Prerequisites -->
    <section id="prerequisites" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
            Prerequisites
        </h2>
        <ul class="doc-list">
            <li>A Google Cloud Console project</li>
            <li>Google Calendar API enabled on that project</li>
            <li>OAuth 2.0 credentials of type "Web application"</li>
            <li>The Laravel scheduler cron, which drives the inbound poll and the channel renewals</li>
            <li>For near-real-time inbound sync, the app reachable at a public HTTPS URL on a domain you have verified with Google (Google will not create a change channel that points at a private address, a plain-HTTP address or an unverified domain)</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">No public URL?</div>
            <p>Installs without a public HTTPS URL still sync both ways. Instead of near-real-time webhook notifications, inbound changes are picked up by the 15-minute <code class="doc-inline-code">google:sync</code> polling fallback, which needs only the scheduler cron.</p>
        </div>
    </section>

    <!-- Setup Instructions -->
    <section id="setup" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Setup Instructions
        </h2>

        <h3 id="setup-console" class="doc-subheading">1. Google Cloud Console Setup</h3>
        <ol class="doc-list doc-list-numbered">
            <li>Go to the <a href="https://console.cloud.google.com/" target="_blank" rel="noopener noreferrer" class="doc-link">Google Cloud Console</a></li>
            <li>Create a new project or select an existing one</li>
            <li>Go to "APIs &amp; Services" &gt; "Library"</li>
            <li>Search for "Google Calendar API"</li>
            <li>Click on it and press "Enable"</li>
        </ol>

        <h3 id="setup-consent" class="doc-subheading">2. OAuth Consent Screen and Scopes</h3>
        <p>Configure the consent screen and add the scopes Event Schedule requests. Anything missing here shows up later as a failed sync rather than a failed sign-in.</p>
        <ul class="doc-list">
            <li><code class="doc-inline-code">https://www.googleapis.com/auth/calendar.events</code> to create, update and delete events</li>
            <li><code class="doc-inline-code">https://www.googleapis.com/auth/calendar.readonly</code> to list calendars and read events for inbound sync and for the import page</li>
            <li><code class="doc-inline-code">openid</code>, <code class="doc-inline-code">email</code> and <code class="doc-inline-code">profile</code> to identify the connecting account</li>
        </ul>
        <p>A connection started on the import page asks for read access only: <code class="doc-inline-code">calendar.readonly</code> and the three identity scopes. Every other connection asks for all five.</p>
        <p>While the project is in testing mode, add each account that will connect a calendar as a test user. Event Schedule always requests offline access and forces the consent prompt, so a refresh token is issued on every connect.</p>

        <h3 id="setup-credentials" class="doc-subheading">3. OAuth 2.0 Credentials</h3>
        <ol class="doc-list doc-list-numbered">
            <li>Go to "APIs &amp; Services" &gt; "Credentials"</li>
            <li>Click "Create Credentials" &gt; "OAuth 2.0 Client IDs"</li>
            <li>Choose "Web application" as the application type</li>
            <li>Add the authorized redirect URI that calendar sync and the import page both use: <code class="doc-inline-code">https://yourdomain.com/google-calendar/callback</code> for production, or <code class="doc-inline-code">http://localhost:8000/google-calendar/callback</code> for development</li>
            <li>Add the three redirect URIs of the Google sign-in buttons, on the same host: <code class="doc-inline-code">/auth/google/callback</code>, <code class="doc-inline-code">/auth/google/connect/callback</code> and <code class="doc-inline-code">/auth/google/set-password/callback</code></li>
            <li>Save the credentials and note down the Client ID and Client Secret</li>
        </ol>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Google sign-in comes with it</div>
            <p>The same Client ID and Client Secret power signing in with Google, and it is not a separate switch: once <code class="doc-inline-code">GOOGLE_CLIENT_ID</code> is set, the log in page shows <strong>Log in with Google</strong>, the sign-up page shows <strong>Continue with Google</strong> where registration is open, and <strong>Google Settings</strong> offers <strong>Connect Google Account</strong>. Without the three redirect URIs above, Google refuses those buttons with a redirect URI mismatch. Signing in with Google and connecting Google Calendar stay separate actions, and a user can do either one without the other.</p>
        </div>

        <h3 id="setup-env" class="doc-subheading">4. Environment Configuration</h3>
        <p>Add the following environment variables to your <code class="doc-inline-code">.env</code> file. <code class="doc-inline-code">.env.example</code> carries the same four, commented out, in its "Google Calendar sync (optional)" block:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">GOOGLE_CLIENT_ID</span>=<span class="code-string">your_google_client_id</span>
<span class="code-variable">GOOGLE_CLIENT_SECRET</span>=<span class="code-string">your_google_client_secret</span>
<span class="code-variable">GOOGLE_REDIRECT_URI</span>=<span class="code-string">https://yourdomain.com/google-calendar/callback</span>
<span class="code-variable">GOOGLE_WEBHOOK_SECRET</span>=<span class="code-string">a_long_random_string</span></code></pre>
        </div>

        <p>If you cache your configuration, run <code class="doc-inline-code">php artisan config:cache</code> after editing <code class="doc-inline-code">.env</code>, or the new values will not be picked up.</p>

        <h3 id="variable-reference" class="doc-subheading">Variable Reference</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">GOOGLE_CLIENT_ID</code></td>
                        <td>The Client ID of the "Web application" OAuth client. Setting it is also what shows the Google sign-in buttons and the Google Calendar source on the import page</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GOOGLE_CLIENT_SECRET</code></td>
                        <td>The Client Secret of the same OAuth client</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GOOGLE_REDIRECT_URI</code></td>
                        <td>Must exactly match a redirect URI registered on the OAuth client (<code class="doc-inline-code">{APP_URL}/google-calendar/callback</code>)</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GOOGLE_WEBHOOK_SECRET</code></td>
                        <td>Required for near-real-time inbound sync; not needed for polling-only installs. Any long random string. It is sent to Google as the channel token, Google echoes it back as the <code class="doc-inline-code">X-Goog-Channel-Token</code> header on every change notification, and Event Schedule rejects any notification whose value does not match. Leave it empty and inbound changes only arrive on the 15-minute poll</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="setup-cron" class="doc-subheading">5. Scheduler Cron</h3>
        <p>Inbound polling and channel renewal run through the Laravel scheduler, so the standard cron entry from <a href="{{ route('marketing.docs.selfhost.installation') }}#cron" class="doc-link">Installation</a> has to be in place:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>crontab</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>* * * * * php artisan schedule:run</code></pre>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            Features
        </h2>

        <h3 id="how-sync-works" class="doc-subheading">How Sync Works</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Part</th>
                        <th>What happens</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Connect an account</td>
                        <td>Each user connects their own Google account under <strong>Settings</strong> &rarr; <strong>Integrations</strong> &rarr; <strong>Google Settings</strong>, with <strong>Connect Google Calendar</strong>. The tokens are stored on that user record</td>
                    </tr>
                    <tr>
                        <td>Pick a calendar and a direction</td>
                        <td>The schedule's owner chooses one of their calendars and a sync direction in the <strong>Google Calendar</strong> row of <strong>Edit Schedule</strong> &rarr; <strong>Integrations</strong>. The setting belongs to the schedule, so two schedules on the same account can behave differently</td>
                    </tr>
                    <tr>
                        <td>Outbound</td>
                        <td>Publishing, editing, cancelling or deleting an event pushes the change to the selected calendar, with no extra step</td>
                    </tr>
                    <tr>
                        <td>Inbound</td>
                        <td>Google change notifications post to the webhook endpoint, and Event Schedule reads the changes with an incremental sync</td>
                    </tr>
                    <tr>
                        <td>Polling fallback</td>
                        <td>The 15-minute <code class="doc-inline-code">google:sync</code> command catches anything the notifications miss, and is the only inbound path on installs without a public URL</td>
                    </tr>
                    <tr>
                        <td>Channel renewal</td>
                        <td>The daily <code class="doc-inline-code">google:refresh-webhooks</code> command replaces change channels within three days of expiring</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="sync-direction" class="doc-subheading">Sync Direction</h3>
        <p>Each schedule picks one of four options under <strong>Sync Direction</strong>. Saving the schedule is what applies the choice and sets up the change channel. Only the schedule's owner sees the controls, and only while their own Google account is connected.</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Option</th>
                        <th>What it does</th>
                        <th>Change channel</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>To Google Calendar</td>
                        <td>Published Event Schedule events appear in Google Calendar</td>
                        <td>Not needed, and an existing one is removed</td>
                    </tr>
                    <tr>
                        <td>From Google Calendar</td>
                        <td>Events from Google Calendar are imported into Event Schedule</td>
                        <td>Created, so edits arrive quickly</td>
                    </tr>
                    <tr>
                        <td>Bidirectional Sync</td>
                        <td>Both of the above. New events and edits travel in both directions. Deleting an event here removes its Google copy; deleting one in Google follows <a href="#delete-sync" class="doc-link">the deletion setting</a></td>
                        <td>Created</td>
                    </tr>
                    <tr>
                        <td>No Sync</td>
                        <td>Google Calendar synchronization is off for the schedule. Nothing is pushed, and inbound notifications are ignored. Events already on the calendar are left alone</td>
                        <td>Any existing channel stays registered but is no longer acted on</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="what-is-synced" class="doc-subheading">Event Information Synced</h3>
        <p>A Google Calendar entry created by Event Schedule carries:</p>
        <ul class="doc-list">
            <li>The event name, as the Google event title</li>
            <li>The event description. If the schedule has a <strong>Calendar Description Template</strong> set under <strong>Integrations</strong> &rarr; <strong>Calendar text and feeds</strong>, the rendered template is sent instead (see the <a href="{{ route('marketing.docs.creating_schedules') }}#available-variables" class="doc-link">available variables</a>). On an update with no template and an empty description, no description is sent, so notes you typed on the Google copy survive</li>
            <li>Start and end times in the schedule's timezone. The end is the start plus the event duration, and two hours when the event has no stored duration</li>
            <li>Location, taken from the venue's best available address. Events with no venue are sent without a location</li>
            <li>Google visibility: public for normal events, private for unlisted ones</li>
        </ul>

        <h3 id="not-sent" class="doc-subheading">What Is Not Sent</h3>
        <ul class="doc-list">
            <li><strong>Everything else about the event.</strong> Only the fields above leave Event Schedule: images, ticket types, prices and attendees stay here</li>
            <li><strong>Drafts.</strong> A Draft or Internal event is never pushed by a save, so an event first appears on the calendar when you publish it</li>
            <li><strong>Recurrence.</strong> Event Schedule does not send a recurrence rule, so a recurring event becomes a single Google entry on the series start date rather than a repeating series. Use the schedule's <a href="{{ route('marketing.docs.sharing') }}#calendar-feeds" class="doc-link">iCal feed</a> or the .ics download when you need every date of a series in a calendar app</li>
        </ul>

        <h3 id="inbound-events" class="doc-subheading">Events That Arrive From Google</h3>
        <p>Inbound sync expands Google's recurring events first, so each occurrence arrives as its own event. Events brought in this way:</p>
        <ul class="doc-list">
            <li>Arrive already approved, and use the schedule's slug pattern and default category</li>
            <li>Take their name, description, start time and duration from the Google entry. The description is converted from HTML to Markdown</li>
            <li>Convert the Google location into a venue, reusing one of your existing venues when the name or address matches and creating one when nothing matches. An event that already has a venue keeps it</li>
            <li>Are matched to an existing event by name and start time when there is no stored mapping yet, so an event you pushed out does not come back as a duplicate</li>
            <li>Never overwrite an appointment booking. An event created by a booking is owned by Event Schedule: inbound sync does not rewrite its name, description or time, so moving the Google copy will not move a customer's booking</li>
        </ul>

        <h3 id="realtime" class="doc-subheading">Real-Time Sync and Polling Fallback</h3>
        <ul class="doc-list">
            <li>Saving a schedule with an inbound direction creates a Google change channel that posts to the webhook endpoint, so calendar edits show up within moments. If the channel cannot be created the save still succeeds, the failure is logged, and inbound changes fall back to the poll</li>
            <li>The 15-minute <code class="doc-inline-code">google:sync</code> command polls for changes as a fallback, and is the main path on installs with no public URL. It uses the schedule owner's connected account</li>
            <li>The daily <code class="doc-inline-code">google:refresh-webhooks</code> command replaces channels that are within three days of expiring</li>
            <li>Inbound sync is incremental: Event Schedule stores Google's sync cursor, so each run fetches only what changed. If Google rejects the stored cursor, which happens after a long gap or a calendar switch, one full sync runs to rebuild it</li>
            <li>The first full sync covers a window from 30 days ago to 365 days ahead</li>
            <li>Inbound work is serialized per schedule, so the webhook and the poll cannot import the same event twice</li>
        </ul>

        <h3 id="delete-sync" class="doc-subheading">When an Event Is Deleted in the Connected Calendar</h3>
        <p>Schedules that sync inbound also choose <strong>When an event is deleted in the connected calendar</strong>, shown right under the sync direction once the direction is From Google Calendar or Bidirectional Sync. It is one setting for the schedule, shared with the Outlook Calendar integration: while your Google Calendar is connected it is shown in this row and not in the Outlook one.</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>What happens in Event Schedule</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Keep it here</td>
                        <td>Nothing. The event stays exactly as it is. This is the default</td>
                    </tr>
                    <tr>
                        <td>Mark as cancelled</td>
                        <td>The event is marked cancelled rather than removed, so the record and its history survive. This is reversible, and is the right choice when tickets have been sold</td>
                    </tr>
                    <tr>
                        <td>Delete it here</td>
                        <td>The event is deleted, which cannot be undone. Events with ticket sales or ad boost spend are marked cancelled instead, so their records are never destroyed</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p>Only a real deletion in Google Calendar triggers the policy, and only through the incremental sync, so the schedule needs at least one completed inbound sync first. Marking an event cancelled this way does not notify anyone: the cancellation notice that <strong>Cancel event</strong> in the event editor can send to ticket buyers and to the event's interest list only goes out from there.</p>
        <p>An event that belongs to more than one schedule is only detached from this schedule, never cancelled or deleted outright, unless this schedule is the one that owns it. The other schedules keep the event as it is.</p>

        <h3 id="calendar-sync-tab" class="doc-subheading">An Event's Calendar Sync Tab</h3>
        <p>Once a schedule has a calendar selected and a direction that includes To Google Calendar, the form of a saved event gains a <strong>Calendar sync</strong> tab with a <strong>Google Calendar</strong> row. It shows the state of this event on the calendar of the schedule you are editing it under:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>What it means, and what the row offers</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Synced</td>
                        <td>The event has a copy on the calendar, and Event Schedule remembers which calendar it lives on. <strong>Remove</strong> takes the copy off the calendar, after a confirmation</td>
                    </tr>
                    <tr>
                        <td>Not synced</td>
                        <td>This event has no copy on the calendar yet. The line under it says why: "It is copied the next time you save." for a published event, and "Only an event guests can see is copied to the calendar." for a Draft or Internal one. <strong>Sync Now</strong> creates the copy at once</td>
                    </tr>
                    <tr>
                        <td>Tab absent</td>
                        <td>The event has not been saved yet, the schedule has no calendar selected, or its direction does not include To Google Calendar</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p>The status is the schedule owner's copy, while <strong>Sync Now</strong> and <strong>Remove</strong> act with the Google account of whoever is signed in, so they are the owner's buttons to use. If a request fails, the reason is shown in the row. The same tab carries an <strong>Outlook Calendar</strong> row when the schedule pushes to Outlook too.</p>

        <h3 id="member-sync" class="doc-subheading">Personal Calendar Sync for Members</h3>
        <p>A team member who is not the schedule owner sees a <strong>Sync to My Calendar</strong> block in the schedule's <strong>Google Calendar</strong> row. Choosing one of their own calendars mirrors the schedule's events into it, in addition to whatever the owner has configured, and the member gets the same create, update and delete operations even when the owner has left the schedule on No Sync. Turning it back off removes the copies the member received.</p>
        <ul class="doc-list">
            <li>The member needs their own connected Google account, with a connection that may write (see <a href="#read-only" class="doc-link">read-only connections</a>)</li>
            <li>The block has its own <strong>Save</strong> button, separate from the schedule form</li>
            <li>Turning it on does not backfill: events already on the schedule reach the member's calendar the next time they are edited</li>
            <li>This is a Google-only feature, since the Outlook and CalDAV integrations sync the owner's calendar only</li>
            <li>Adding team members beyond the owner is an Enterprise feature on the hosted service, and a selfhosted install resolves to Enterprise</li>
        </ul>

        <h3 id="read-only" class="doc-subheading">Read-Only Connections</h3>
        <p>A Google connection made from the import page asks Google for read access only. It can import, and it can sync From Google Calendar, but it cannot add or change anything on a Google calendar. While the owner's connection is read-only:</p>
        <ul class="doc-list">
            <li>In the <strong>Google Calendar</strong> row, <strong>To Google Calendar</strong> and <strong>Bidirectional Sync</strong> are switched off under a notice that reads "This connection can only read your Google Calendar." <strong>Allow at Google</strong> in that notice runs the full consent and comes back to the row</li>
            <li>Nothing is pushed on save, and <strong>Sync Now</strong>, <strong>Sync Events</strong> with an outbound direction and turning on <strong>Sync to My Calendar</strong> are refused with the same notice</li>
            <li>A schedule saved in that state stores Bidirectional Sync as From Google Calendar, and To Google Calendar as No Sync</li>
        </ul>
        <p>Connections made before Event Schedule began recording what Google granted count as full connections.</p>

        <h3 id="one-time-import" class="doc-subheading">One-Time Import From Google Calendar</h3>
        <p>The import page has its own <strong>Google Calendar</strong> source, for events that live in a calendar that is not public. It is separate from sync: the schedule's owner connects with read access, picks one calendar, and chooses which of its upcoming events to copy. Nothing is written to Google, and events added to that calendar later do not arrive by themselves.</p>
        <p>The source appears for a schedule's owner once <code class="doc-inline-code">GOOGLE_CLIENT_ID</code> is set, and it uses the same OAuth client and redirect URI as sync, so there is nothing more to configure. The walkthrough for owners is <a href="{{ route('marketing.docs.ai_import') }}#google-import" class="doc-link">Importing from Google Calendar</a>.</p>
    </section>

    <!-- Usage -->
    <section id="usage" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
            </svg>
            Usage
        </h2>

        <h3 id="step-by-step" class="doc-subheading">Step by Step</h3>
        <p>Steps 1 and 2 turn sync on. The rest are for events that existed before it was on, for a change of calendar, and for team members.</p>

        <ol class="doc-steps">
            <li class="doc-step">
                <h4 class="doc-step-title">Connect Google Calendar</h4>
                <ol class="doc-list doc-list-numbered">
                    <li>Open <strong>Settings</strong> in the sidebar and choose the <strong>Integrations</strong> tab</li>
                    <li>In <strong>Google Settings</strong>, under <strong>Google Calendar</strong>, click <strong>Connect Google Calendar</strong></li>
                    <li>Approve the request on Google's consent screen. You come back to the same section, which now reads <strong>Google Calendar Connected</strong></li>
                </ol>
                <p>The <strong>Google Account</strong> line above it is the separate sign-in connection. Connecting there does not connect a calendar, and you can use either one without the other. Connecting is half of it: nothing syncs until a schedule is told to.</p>
            </li>

            <li class="doc-step">
                <h4 class="doc-step-title">Choose a Calendar and Sync Direction</h4>
                <ol class="doc-list doc-list-numbered">
                    <li>Open the schedule, click <strong>Edit Schedule</strong>, choose the <strong>Integrations</strong> tab and open the <strong>Google Calendar</strong> row</li>
                    <li>Pick the calendar to sync with under <strong>Select Google Calendar</strong></li>
                    <li>Choose a <strong>Sync Direction</strong>: To Google Calendar, From Google Calendar, Bidirectional Sync or No Sync</li>
                    <li>With From Google Calendar or Bidirectional Sync selected, choose <strong>When an event is deleted in the connected calendar</strong>: Keep it here, Mark as cancelled, or Delete it here. Keep it here is the default</li>
                    <li>Click <strong>Save</strong></li>
                </ol>
                <p>Saving is what applies the selection and sets up the change channel. It does not push the events you already have: use step 3, 4 or 5 for those. Only the schedule's owner sees these controls. An owner who has not connected yet sees a <strong>Connect Google Calendar</strong> button in the row instead, which opens Settings.</p>
            </li>

            <li class="doc-step">
                <h4 class="doc-step-title">Sync a Single Event</h4>
                <p>Open the event's form and choose the <strong>Calendar sync</strong> tab. In the <strong>Google Calendar</strong> row, click <strong>Sync Now</strong>, or <strong>Remove</strong> to take the copy back off the calendar. The tab appears once the schedule has a calendar selected and pushes to Google.</p>
            </li>

            <li class="doc-step">
                <h4 class="doc-step-title">Sync the Whole Schedule</h4>
                <p>Open the schedule, click <strong>Actions</strong> and choose <strong>Sync Events</strong>, then confirm. The entry is listed for the schedule's owner once their Google account is connected and the schedule has a calendar selected.</p>
                <ul class="doc-list">
                    <li>It runs the schedule's saved sync direction now. A schedule left on No Sync is pushed to Google instead, and To Google Calendar is then saved as its direction</li>
                    <li>A push only creates events that are missing from the calendar; it does not rewrite copies that are already there</li>
                    <li>An inbound direction also imports from the calendar, exactly as the scheduled poll would</li>
                </ul>
            </li>

            <li class="doc-step">
                <h4 class="doc-step-title">Resync Everything to Google</h4>
                <p>Use this after switching Google accounts or target calendars.</p>
                <ol class="doc-list doc-list-numbered">
                    <li>Save the schedule with the new calendar selected. The resync pushes to the saved calendar, and the button stays disabled while the dropdown differs from it</li>
                    <li>In the schedule's <strong>Google Calendar</strong> row, click <strong>Resync to Google Calendar</strong> and confirm</li>
                </ol>
                <ul class="doc-list">
                    <li>Only the schedule owner sees this button, and the request is refused unless the direction includes To Google Calendar</li>
                    <li>Events already sitting on the saved calendar are left alone. An event whose copy is on a different calendar has that old copy deleted and a fresh one created, so switching calendars does not leave duplicates behind</li>
                    <li>It only ever pushes to Google and never imports, and it only covers published events</li>
                    <li>The resync works through the schedule ten events at a time, each batch queuing the next. With <code class="doc-inline-code">QUEUE_CONNECTION=database</code>, the scheduler's minutely queue worker carries it to the end, which can take a few minutes on a large schedule. On the shipped <code class="doc-inline-code">sync</code> setting the batches run one after another once the page has responded, in the same PHP process, so a very large schedule is better served by <code class="doc-inline-code">database</code>. Clicking it again is safe: it only redoes work still outstanding</li>
                </ul>
            </li>

            <li class="doc-step">
                <h4 class="doc-step-title">Mirror a Schedule into Your Own Calendar</h4>
                <ol class="doc-list doc-list-numbered">
                    <li>As a team member who does not own the schedule, connect your own Google Calendar first (step 1)</li>
                    <li>Open the schedule's <strong>Edit Schedule</strong> &rarr; <strong>Integrations</strong> &rarr; <strong>Google Calendar</strong> row and find <strong>Sync to My Calendar</strong></li>
                    <li>Pick one of your calendars under <strong>Select Your Calendar</strong> and click <strong>Save</strong>. That block saves on its own, so you do not need to save the schedule</li>
                </ol>
                <p>Choosing <strong>No Sync</strong> there and saving removes the events again.</p>
            </li>
        </ol>

        <h3 id="automatic-sync" class="doc-subheading">Automatic Sync</h3>
        <p>Once a schedule pushes to Google, its events are synced automatically when they are:</p>
        <ul class="doc-list">
            <li>Published, whether that is a new event or a draft you just published</li>
            <li>Edited, which updates the existing Google entry in place</li>
            <li>Deleted, cancelled or turned back into a draft, which removes the copy from the calendar. Restoring a cancelled event puts it back</li>
        </ul>
        <p>Drafts are never pushed, so an event only reaches the calendar once it is published. Members with personal calendar sync receive the same create, update and delete operations even when the owner has not turned on sync for the schedule.</p>

        <h3 id="developers" class="doc-subheading">For Developers: Sync Helpers on the Event Model</h3>
        <p>Outbound sync and status checks go through the <code class="doc-inline-code">Event</code> model:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>PHP</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment">// Push to every schedule that syncs to Google, plus members with personal sync</span>
<span class="code-variable">$event</span>-><span class="code-keyword">syncToGoogleCalendar</span>(<span class="code-string">'create'</span>); <span class="code-comment">// or 'update', 'delete'</span>

<span class="code-comment">// Is there a Google copy for a given schedule?</span>
<span class="code-variable">$event</span>-><span class="code-keyword">isSyncedToGoogleCalendarForRole</span>(<span class="code-variable">$role</span>->id);
<span class="code-variable">$event</span>-><span class="code-keyword">isSyncedToGoogleCalendarForSubdomain</span>(<span class="code-variable">$subdomain</span>);

<span class="code-comment">// 'not_connected', 'not_synced' or 'synced'</span>
<span class="code-variable">$event</span>-><span class="code-keyword">getGoogleCalendarSyncStatus</span>(<span class="code-variable">$user</span>, <span class="code-variable">$role</span>->id);</code></pre>
        </div>

        <h3 id="dispatch" class="doc-subheading">For Developers: How the Work Is Dispatched</h3>
        <p>The mapping between an event and its Google copy lives in the <code class="doc-inline-code">calendar_syncs</code> table, one row per user, event and schedule, together with the calendar the copy was created on.</p>
        <ul class="doc-list">
            <li><code class="doc-inline-code">SyncEventToGoogleCalendar</code> performs one create, update or delete. Saving an event runs it inline, so the calendar is up to date by the time the save finishes and a queue worker is not required</li>
            <li><code class="doc-inline-code">ForceResyncGoogleCalendar</code> backs the <strong>Resync to Google Calendar</strong> button and is queued. It handles ten events per run and dispatches a follow-up while any remain, so a large schedule finishes across several runs instead of timing out. On the <code class="doc-inline-code">sync</code> connection each follow-up waits until the page has responded, by which point the batch before it has released its overlap lock, so the batches run back to back in the same PHP process</li>
            <li>Inbound sync is serialized per schedule with a lock, so the webhook and the 15-minute poll cannot import the same event twice</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Queue worker</div>
            <p>Everyday sync does not need a queue: saving an event pushes it inline, and a change notification from Google is handled inside the webhook request. The bulk resync runs without one too. A very large schedule is better served by <code class="doc-inline-code">QUEUE_CONNECTION=database</code>, where the scheduler's minutely queue worker runs the batches, with no separate <code class="doc-inline-code">php artisan queue:work</code> needed.</p>
        </div>
    </section>

    <!-- API Endpoints -->
    <section id="api-endpoints" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
            </svg>
            API Endpoints
        </h2>

        <p>These are the application's own session-authenticated routes, not part of the public REST API. Every route except the two webhook routes requires a signed-in user with a verified email address. The webhook routes are public, and are authenticated by the channel token instead.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Endpoint</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">GET /google-calendar/redirect</code></td>
                        <td>Start the OAuth flow. With <code class="doc-inline-code">from=import</code> and a schedule's <code class="doc-inline-code">subdomain</code> it asks for read access only and returns to the import page; with <code class="doc-inline-code">from=settings</code> it returns to the schedule's Google Calendar row. Both are honoured for the schedule's owner only</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GET /google-calendar/callback</code></td>
                        <td>OAuth callback</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GET /google-calendar/reauthorize</code></td>
                        <td>Re-run consent to obtain a refresh token</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /google-calendar/disconnect</code></td>
                        <td>Disconnect Google Calendar: remove the change channels, revoke the grant at Google and clear the tokens and sync records</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GET /google-calendar/calendars</code></td>
                        <td>Get the connected account's calendars</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GET /google-calendar/import/{subdomain}/calendars</code></td>
                        <td>The calendars the import page offers. Owner only, and rate limited to 30 requests per minute</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /google-calendar/import/{subdomain}/events</code></td>
                        <td>One calendar's upcoming events, as the import page's list to choose from. Nothing is added by this call. Owner only, rate limited to 30 requests per minute, and to 10 lists per minute per user together with link imports</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /google-calendar/sync/{subdomain}</code></td>
                        <td>Sync a schedule in the direction given by <code class="doc-inline-code">sync_direction</code> (<code class="doc-inline-code">to</code>, <code class="doc-inline-code">from</code> or <code class="doc-inline-code">both</code>), and optionally save that direction and the deletion policy. Owner only</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /google-calendar/force-sync-to-google/{subdomain}</code></td>
                        <td>Queue a full push of a schedule to Google. Owner only, and rate limited to 5 requests per minute</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /google-calendar/member-sync/{subdomain}</code></td>
                        <td>Turn a member's personal calendar sync on or off. For members other than the owner</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /google-calendar/sync-event/{subdomain}/{eventId}</code></td>
                        <td>Sync one event, with the caller's own Google connection</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">DELETE /google-calendar/unsync-event/{subdomain}/{eventId}</code></td>
                        <td>Remove one event from Google Calendar</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GET /google-calendar/webhook</code></td>
                        <td>Channel verification challenge, echoed back to Google. Rate limited to 10 requests per minute</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /google-calendar/webhook</code></td>
                        <td>Change notification handler, authenticated by the channel token. Rate limited to 60 requests per minute</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="scheduled-commands" class="doc-subheading">Scheduled Commands</h3>
        <p>These Artisan commands keep inbound sync and the change channels healthy:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Command</th>
                        <th>Frequency</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">google:sync</code></td>
                        <td>Every 15 minutes</td>
                        <td>Polls Google for changes on every schedule whose direction is From Google Calendar or Bidirectional Sync, using each schedule owner's account. Accepts <code class="doc-inline-code">--role=</code> with a schedule id to limit it to one schedule</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">google:refresh-webhooks</code></td>
                        <td>Daily</td>
                        <td>Replaces change channels within three days of expiring. Accepts <code class="doc-inline-code">--force</code> to rebuild them all, and <code class="doc-inline-code">--role=</code> with a schedule id or subdomain</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Troubleshooting -->
    <section id="troubleshooting" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            Troubleshooting
        </h2>

        <h3 id="common-issues" class="doc-subheading">Common Issues</h3>
        <p>Pick the line that matches what you see.</p>

        <div class="doc-faqs">
            <x-doc-faq question='"Google Calendar not connected"'>
                <ul class="doc-list">
                    <li>Connect the Google account first, under <strong>Settings</strong> &rarr; <strong>Integrations</strong> &rarr; <strong>Google Settings</strong></li>
                    <li>Connecting a Google account for sign-in is not the same thing; the <strong>Google Calendar</strong> line has its own Connect button</li>
                    <li>Check that <code class="doc-inline-code">GOOGLE_CLIENT_ID</code>, <code class="doc-inline-code">GOOGLE_CLIENT_SECRET</code> and <code class="doc-inline-code">GOOGLE_REDIRECT_URI</code> are set and that the redirect URI matches the one registered in Google Cloud Console</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question='"Google did not give permission to see your calendars"'>
                <ul class="doc-list">
                    <li>Google lets a person untick a permission on its consent screen, and a connection without the calendar permission is not kept</li>
                    <li>Connect again and leave the calendar permissions ticked</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question='"This connection can only read your Google Calendar"'>
                <ul class="doc-list">
                    <li>The account was connected from the import page, which asks for read access only</li>
                    <li>Open the schedule's <strong>Google Calendar</strong> row and click <strong>Allow at Google</strong>. See <a href="#read-only" class="doc-link">read-only connections</a></li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question='Repeated re-authorization, or "token refresh failed"'>
                <ul class="doc-list">
                    <li>This means no refresh token was stored. Reconnect the account, which forces the consent prompt again</li>
                    <li>Revoking access to the app in the Google Account security settings and connecting again clears a stuck grant</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="Sync stopped, and the schedule is back on No Sync">
                <ul class="doc-list">
                    <li>Access was removed in the Google account, or the grant lapsed. When Google reports that on the next token refresh, Event Schedule forgets the connection by itself: the tokens, the sync direction, change channel and sync cursor of every schedule that user owns, their event mappings and their calendar selections</li>
                    <li>Events here, and copies already on the Google calendar, are not touched</li>
                    <li>Connect again, then choose the calendar and direction again and save. Copies pushed before are no longer linked to their events, so a push after reconnecting can create them a second time</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="Events do not appear in Google Calendar">
                <ul class="doc-list">
                    <li>The schedule's sync direction has to be To Google Calendar or Bidirectional Sync, and the schedule has to be saved after the change</li>
                    <li>Events that already existed when you turned sync on are not pushed in bulk. Use <strong>Sync Events</strong> from the schedule's <strong>Actions</strong> menu, <strong>Sync Now</strong> on the event's <strong>Calendar sync</strong> tab, or <strong>Resync to Google Calendar</strong></li>
                    <li>Draft and Internal events are never pushed. Publish the event first</li>
                    <li>A read-only connection pushes nothing. See <a href="#read-only" class="doc-link">read-only connections</a></li>
                    <li>Confirm the Google Calendar API is enabled on the project and that the account granted the calendar scopes</li>
                    <li>Check the logs for the failing call</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="The Calendar sync tab is missing on the event form">
                <ul class="doc-list">
                    <li>It only appears on a saved event, when the schedule has a calendar selected and pushes to Google</li>
                    <li>Select a calendar under <strong>Edit Schedule</strong> &rarr; <strong>Integrations</strong> &rarr; <strong>Google Calendar</strong> and save the schedule</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="Google Calendar changes do not reach Event Schedule">
                <ul class="doc-list">
                    <li>Inbound sync needs From Google Calendar or Bidirectional Sync</li>
                    <li>For real-time notifications, the app needs a public HTTPS URL on a domain verified with Google, and <code class="doc-inline-code">GOOGLE_WEBHOOK_SECRET</code> must be set; notifications with a mismatched token are rejected. When Google refuses the channel the save still succeeds and the error is only visible in the log</li>
                    <li>Without a public URL, rely on the 15-minute <code class="doc-inline-code">google:sync</code> poll and confirm the scheduler cron is running</li>
                    <li>Run <code class="doc-inline-code">php artisan google:sync --role=</code> with the schedule id to test a single schedule by hand</li>
                    <li>Inbound sync runs on the schedule owner's Google account, so it stops if the owner disconnects even when other members are still connected</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question='"Resync to Google Calendar" is greyed out or refused'>
                <ul class="doc-list">
                    <li>The button is disabled while the calendar dropdown differs from the saved calendar. Save the schedule first</li>
                    <li>Only the schedule owner sees the button, and the server refuses the request unless the direction includes To Google Calendar</li>
                    <li>The job runs in batches of ten. On the shipped <code class="doc-inline-code">sync</code> setting they run one after another once the page has responded, in the same PHP process, so a very large schedule is better served by <code class="doc-inline-code">QUEUE_CONNECTION=database</code> and the scheduler cron</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="Inbound sync stopped after a while">
                <ul class="doc-list">
                    <li>Change channels expire. The daily <code class="doc-inline-code">google:refresh-webhooks</code> command renews them, so make sure the scheduler is running</li>
                    <li><code class="doc-inline-code">php artisan google:refresh-webhooks --force</code> rebuilds them immediately</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="Leftover events after switching calendars or accounts">
                <ul class="doc-list">
                    <li>Save the schedule with the new calendar selected, then run <strong>Resync to Google Calendar</strong> so old copies are removed and fresh ones are created</li>
                    <li>Events synced before Event Schedule started recording which calendar each copy lived on cannot be cleaned up automatically. Delete those few leftovers in Google Calendar by hand</li>
                    <li>Nothing is removed from the calendar of an account that has already been disconnected, because the app no longer holds a token for it</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="Deleting in Google removed nothing here, or removed too much">
                <ul class="doc-list">
                    <li>The deletion policy defaults to Keep it here, which is why nothing changes locally</li>
                    <li>The setting is only shown while the direction is From Google Calendar or Bidirectional Sync</li>
                    <li>Deletions arrive only through the incremental sync, so the schedule must have completed at least one inbound sync first</li>
                    <li>Delete it here cannot be undone. Choose Mark as cancelled if you may want the event back</li>
                </ul>
            </x-doc-faq>
        </div>

        <h3 id="logs" class="doc-subheading">Logs</h3>
        <p>Sync operations are logged in the application logs. Check <code class="doc-inline-code">storage/logs/laravel.log</code> for detailed information about sync operations, and <code class="doc-inline-code">storage/logs/scheduler.log</code> for the scheduled sync and channel-renewal runs.</p>
    </section>

    <!-- Security Considerations -->
    <section id="security" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            Security Considerations
        </h2>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Safeguard</th>
                        <th>What it does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Token storage</td>
                        <td>Google access and refresh tokens are stored per user and encrypted at rest with the install's <code class="doc-inline-code">APP_KEY</code>, and they are hidden from the model's serialized output</td>
                    </tr>
                    <tr>
                        <td>Scope limitation</td>
                        <td>Only the calendar scopes the integration needs are requested, plus <code class="doc-inline-code">openid</code>, <code class="doc-inline-code">email</code> and <code class="doc-inline-code">profile</code> to identify the account. A connection started on the import page asks for read access only</td>
                    </tr>
                    <tr>
                        <td>OAuth state check</td>
                        <td>The connect flow carries a random state value that is verified on the callback, so a forged callback is rejected</td>
                    </tr>
                    <tr>
                        <td>Webhook authentication</td>
                        <td><code class="doc-inline-code">GOOGLE_WEBHOOK_SECRET</code> is the channel token Google echoes back, and mismatched notifications are rejected. Both webhook routes are rate limited</td>
                    </tr>
                    <tr>
                        <td>Owner-only controls</td>
                        <td>A schedule's calendar, its sync direction, its deletion setting, <strong>Sync Events</strong>, the full resync and the one-time import belong to the schedule's owner, because the standing sync runs on the owner's Google account. A team member's own calendar goes through <strong>Sync to My Calendar</strong></td>
                    </tr>
                    <tr>
                        <td>Token refresh</td>
                        <td>Access tokens are refreshed automatically before each call that needs one</td>
                    </tr>
                    <tr>
                        <td>Clean disconnect</td>
                        <td>Disconnecting removes the change channels, revokes the grant at Google and turns sync off for the schedules the user owns, clears their sync cursors, and deletes the stored tokens, the user's event mappings and their calendar selection on every schedule they belong to. The same clean-up runs by itself when Google reports the grant withdrawn. It does not delete anything already on the Google calendar</td>
                    </tr>
                    <tr>
                        <td>Audit trail</td>
                        <td>Connecting, disconnecting, syncing a schedule and toggling personal member sync are all written to the audit log</td>
                    </tr>
                    <tr>
                        <td>Secrets in <code class="doc-inline-code">.env</code></td>
                        <td>Keep the client secret and webhook secret in <code class="doc-inline-code">.env</code>, and never commit them to source control</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</x-docs-page>
