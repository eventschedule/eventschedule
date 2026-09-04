<x-docs-page
    key="selfhost/google-calendar"
    description="Set up bidirectional Google Calendar sync with Event Schedule. Automatically sync events between both platforms."
    lede="Set up and use the Google Calendar integration for two-way sync between Event Schedule and Google Calendar."
>
    <x-slot:toc>
        <x-doc-nav-link href="#prerequisites">Prerequisites</x-doc-nav-link>
        <x-doc-nav-link href="#setup">Setup Instructions</x-doc-nav-link>
        <x-doc-nav-link href="#features">Features</x-doc-nav-link>
        <x-doc-nav-link href="#usage">Usage</x-doc-nav-link>
        <x-doc-nav-link href="#api-endpoints">API Endpoints</x-doc-nav-link>
        <x-doc-nav-link href="#troubleshooting">Troubleshooting</x-doc-nav-link>
        <x-doc-nav-link href="#security">Security Considerations</x-doc-nav-link>
    </x-slot:toc>

    <!-- Prerequisites -->
    <section id="prerequisites" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
            Prerequisites
        </h2>
        <ol class="doc-list doc-list-numbered">
            <li>A Google Cloud Console project</li>
            <li>Google Calendar API enabled on that project</li>
            <li>OAuth 2.0 credentials of type "Web application"</li>
            <li>For near-real-time inbound sync, the app reachable at a public HTTPS URL on a domain you have verified with Google (Google will not create a change channel that points at a private address, a plain-HTTP address or an unverified domain)</li>
            <li>The Laravel scheduler cron, which drives the inbound poll and the channel renewals</li>
        </ol>

        <div class="doc-callout doc-callout-plan mt-6">
            <div class="doc-callout-title">Included on every plan</div>
            <p>Google Calendar sync is a free feature, and a selfhosted install resolves to the Enterprise tier, so nothing on this page is held back by a plan. It does need the environment variables below: without them the Connect button has no credentials to use.</p>
        </div>

        <div class="doc-callout doc-callout-info mt-6">
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

        <h3 class="doc-subheading">1. Google Cloud Console Setup</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to the <a href="https://console.cloud.google.com/" target="_blank" rel="noopener noreferrer" class="doc-link">Google Cloud Console</a></li>
            <li>Create a new project or select an existing one</li>
            <li>Enable the Google Calendar API:
                <ul class="doc-list mt-2 mb-2">
                    <li>Go to "APIs &amp; Services" &gt; "Library"</li>
                    <li>Search for "Google Calendar API"</li>
                    <li>Click on it and press "Enable"</li>
                </ul>
            </li>
        </ol>

        <h3 class="doc-subheading">2. OAuth Consent Screen and Scopes</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Configure the consent screen and add the scopes Event Schedule requests. Anything missing here shows up later as a failed sync rather than a failed sign-in.</p>
        <ul class="doc-list mb-6">
            <li><code class="doc-inline-code">https://www.googleapis.com/auth/calendar.events</code> to create, update and delete events</li>
            <li><code class="doc-inline-code">https://www.googleapis.com/auth/calendar.readonly</code> to list calendars and read events for inbound sync</li>
            <li><code class="doc-inline-code">openid</code>, <code class="doc-inline-code">email</code> and <code class="doc-inline-code">profile</code> to identify the connecting account</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">While the project is in testing mode, add each account that will connect a calendar as a test user. Event Schedule always requests offline access and forces the consent prompt, so a refresh token is issued on every connect.</p>

        <h3 class="doc-subheading">3. OAuth 2.0 Credentials</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to "APIs &amp; Services" &gt; "Credentials"</li>
            <li>Click "Create Credentials" &gt; "OAuth 2.0 Client IDs"</li>
            <li>Choose "Web application" as the application type</li>
            <li>Add authorized redirect URIs:
                <ul class="doc-list mt-2 mb-2">
                    <li>For development: <code class="doc-inline-code">http://localhost:8000/google-calendar/callback</code></li>
                    <li>For production: <code class="doc-inline-code">https://yourdomain.com/google-calendar/callback</code></li>
                </ul>
            </li>
            <li>Save the credentials and note down the Client ID and Client Secret</li>
        </ol>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Also using Google sign-in?</div>
            <p>The same Client ID and Client Secret power the optional "Sign in with Google" flow. If you want that too, register its redirect URIs on the same OAuth client: <code class="doc-inline-code">/auth/google/callback</code>, <code class="doc-inline-code">/auth/google/connect/callback</code> and <code class="doc-inline-code">/auth/google/set-password/callback</code>. Signing in with Google and connecting Google Calendar are separate actions, and a user can do either one without the other.</p>
        </div>

        <h3 class="doc-subheading">4. Environment Configuration</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Add the following environment variables to your <code class="doc-inline-code">.env</code> file:</p>

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

        <h3 class="doc-subheading">Variable Reference</h3>
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
                        <td>The Client ID of the "Web application" OAuth client</td>
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
                        <td>Required for near-real-time inbound sync; not needed for polling-only installs. Any long random string. It is sent to Google as the channel token and echoed back on every notification</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-warning mt-6">
            <div class="doc-callout-title">Webhook secret is required for real-time inbound sync</div>
            <p>Set <code class="doc-inline-code">GOOGLE_WEBHOOK_SECRET</code> to a long random value. Google echoes it back as the <code class="doc-inline-code">X-Goog-Channel-Token</code> header on every change notification, and Event Schedule rejects any notification whose value does not match. Leave it empty and inbound changes only arrive on the 15-minute poll.</p>
        </div>

        <h3 class="doc-subheading">5. Scheduler Cron</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Inbound polling and channel renewal run through the Laravel scheduler, so the standard cron entry has to be in place:</p>
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

        <h3 class="doc-subheading">How Sync Works</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong class="text-gray-900 dark:text-white">Connect an account:</strong> Each user connects their own Google account from <strong class="text-gray-900 dark:text-white">Settings</strong> &rarr; <strong class="text-gray-900 dark:text-white">Google Settings</strong>, in the <strong class="text-gray-900 dark:text-white">Google Calendar</strong> block. The tokens are stored on that user record</li>
            <li><strong class="text-gray-900 dark:text-white">Pick a calendar and a direction:</strong> The schedule owner chooses one of the connected account's calendars and a sync direction on <strong class="text-gray-900 dark:text-white">Integrations</strong> &rarr; <strong class="text-gray-900 dark:text-white">Google Calendar</strong> of the schedule edit page. The setting belongs to the schedule, so two schedules on the same account can behave differently</li>
            <li><strong class="text-gray-900 dark:text-white">Outbound:</strong> Publishing, editing, cancelling or deleting an event pushes the change to the selected calendar, with no extra step</li>
            <li><strong class="text-gray-900 dark:text-white">Inbound:</strong> Google change notifications post to the webhook endpoint, and Event Schedule reads the changes with an incremental sync</li>
            <li><strong class="text-gray-900 dark:text-white">Polling fallback:</strong> The 15-minute <code class="doc-inline-code">google:sync</code> command catches anything the notifications miss, and is the only inbound path on installs without a public URL</li>
            <li><strong class="text-gray-900 dark:text-white">Channel renewal:</strong> The daily <code class="doc-inline-code">google:refresh-webhooks</code> command replaces change channels within three days of expiring</li>
        </ol>

        <h3 class="doc-subheading">Sync Direction</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Each schedule picks one of four options. Saving the schedule is what applies the choice and sets up the change channel.</p>
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
                        <td><span class="font-semibold text-gray-900 dark:text-white">To Google Calendar</span></td>
                        <td>Published Event Schedule events appear in Google Calendar</td>
                        <td>Not needed, and an existing one is removed</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">From Google Calendar</span></td>
                        <td>Events from Google Calendar are imported into Event Schedule</td>
                        <td>Created, so edits arrive quickly</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Bidirectional Sync</span></td>
                        <td>Both of the above. New events, edits and deletions travel in both directions</td>
                        <td>Created</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">No Sync</span></td>
                        <td>Google Calendar synchronization is off for the schedule. Nothing is pushed, and inbound notifications are ignored. Events already on the calendar are left alone</td>
                        <td>Any existing channel stays registered but is no longer acted on</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Event Information Synced</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A Google Calendar entry created by Event Schedule carries:</p>
        <ul class="doc-list mb-6">
            <li>The event name, as the Google event title</li>
            <li>The event description. If the schedule has a <strong class="text-gray-900 dark:text-white">Calendar Description Template</strong> set on <strong class="text-gray-900 dark:text-white">Integrations</strong> &rarr; <strong class="text-gray-900 dark:text-white">Advanced</strong>, the rendered template is sent instead (see the <a href="{{ route('marketing.docs.creating_schedules') }}#available-variables" class="doc-link">available variables</a>). On an update with no template and an empty description, no description is sent, so notes you typed on the Google copy survive</li>
            <li>Start and end times in the schedule's timezone. The end is the start plus the event duration, and two hours when the event has no stored duration</li>
            <li>Location, taken from the venue's best available address. Events with no venue are sent without a location</li>
            <li>Google visibility: public for normal events, private for unlisted ones</li>
        </ul>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">What is not sent</div>
            <p>Only the fields above leave Event Schedule: images, ticket types, prices and attendees stay here. Draft events are never pushed, so an event first appears on the calendar when you publish it. Event Schedule also does not send a recurrence rule, so a recurring event becomes a single Google entry on the series start date rather than a repeating series. Use the schedule's iCal feed or the .ics download when you need every date of a series in a calendar app.</p>
        </div>

        <h3 class="doc-subheading">Importing From Google</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Inbound sync expands Google's recurring events first, so each occurrence arrives as its own event. Imported events:</p>
        <ul class="doc-list mb-6">
            <li>Arrive already approved, and use the schedule's slug pattern and default category</li>
            <li>Take their name, description, start time and duration from the Google entry. The description is converted from HTML to Markdown</li>
            <li>Convert the Google location into a venue, reusing one of your existing venues when the name or address matches and creating one when nothing matches</li>
            <li>Are matched to an existing event by name and start time when there is no stored mapping yet, so an event you pushed out does not come back as a duplicate</li>
        </ul>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Appointment bookings are protected</div>
            <p>An event created by an appointment booking is owned by Event Schedule. Inbound sync never rewrites its name, description or time, so moving the Google copy will not move a customer's booking.</p>
        </div>

        <h3 class="doc-subheading">Per-Event Sync Status</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong class="text-gray-900 dark:text-white">Google Calendar</strong> section of the event edit page shows the state of this event on the calendar of the schedule you are editing it under:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Not synced to Google Calendar</span></td>
                        <td>This event has no copy on the calendar yet. The button reads "Sync to Google Calendar"</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Synced to Google Calendar</span></td>
                        <td>The event has a copy on the calendar, and Event Schedule remembers which calendar it lives on. The button reads "Remove from Google Calendar"</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Section hidden</span></td>
                        <td>The whole section is absent when the schedule has no calendar selected, or its direction does not include To Google Calendar</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">When an Event Is Deleted in the Connected Calendar</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Schedules that sync inbound also choose <strong class="text-gray-900 dark:text-white">When an event is deleted in the connected calendar</strong>, shown right under the sync direction once the direction is From Google Calendar or Bidirectional Sync. The setting is shared with the Outlook Calendar integration, so changing it on either tab changes it for both.</p>
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
                        <td><span class="font-semibold text-gray-900 dark:text-white">Keep it here</span></td>
                        <td>Nothing. The event stays exactly as it is. This is the default</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Mark as cancelled</span></td>
                        <td>The event is marked cancelled rather than removed, so the record and its history survive. This is reversible, and is the right choice when tickets have been sold</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Delete it here</span></td>
                        <td>The event is deleted. Events with ticket sales or ad boost spend are marked cancelled instead, so their records are never destroyed</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mt-4">Only a real deletion in Google Calendar triggers the policy, and only through the incremental sync, so the schedule needs at least one completed inbound sync first.</p>
        <p class="text-gray-600 dark:text-gray-300 mt-4 mb-6">If the deleted copy belonged to a schedule that shares the event with others, that schedule is simply detached and the event stays intact for everyone else.</p>

        <h3 class="doc-subheading">Personal Calendar Sync for Members</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A team member who is not the schedule owner sees a <strong class="text-gray-900 dark:text-white">Sync to My Calendar</strong> block on the schedule's Google Calendar tab. Choosing one of their own calendars mirrors the schedule's events into it, in addition to whatever the owner has configured, and the member gets the same create, update and delete operations even when the owner has left the schedule on No Sync. Turning it back off removes the copies the member received.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The member needs their own connected Google account, and the block has its own Save button, separate from the schedule form. Turning it on does not backfill: events already on the schedule reach the member's calendar the next time they are edited. This is a Google-only feature, since the Outlook and CalDAV integrations sync the owner's calendar only. Adding team members beyond the owner is an Enterprise feature on the hosted service, and a selfhosted install resolves to Enterprise.</p>

        <h3 class="doc-subheading">Real-Time Sync and Polling Fallback</h3>
        <ul class="doc-list">
            <li>Saving a schedule with an inbound direction creates a Google change channel that posts to the webhook endpoint, so calendar edits show up within moments. If the channel cannot be created the save still succeeds, the failure is logged, and inbound changes fall back to the poll</li>
            <li>The 15-minute <code class="doc-inline-code">google:sync</code> command polls for changes as a fallback, and is the main path on installs with no public URL. It uses the schedule owner's connected account</li>
            <li>The daily <code class="doc-inline-code">google:refresh-webhooks</code> command replaces channels that are within three days of expiring</li>
            <li>Inbound sync is incremental: Event Schedule stores Google's sync cursor, so each run fetches only what changed. If Google rejects the stored cursor, which happens after a long gap or a calendar switch, one full sync runs to rebuild it</li>
            <li>The first full sync covers a window from 30 days ago to 365 days ahead</li>
            <li>Inbound work is serialized per schedule, so the webhook and the poll cannot import the same event twice</li>
        </ul>
    </section>

    <!-- Usage -->
    <section id="usage" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
            </svg>
            Usage
        </h2>

        <h3 class="doc-subheading">Step by Step</h3>

        <div class="space-y-6 mb-8">
            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">1. Connect Google Calendar</h4>
                <ol class="doc-list doc-list-numbered text-sm">
                    <li>Go to your settings page (<code class="doc-inline-code">/settings</code>)</li>
                    <li>Open the "Google Settings" section</li>
                    <li>Under "Google Calendar", click "Connect Google Calendar"</li>
                    <li>Authorize the application in the Google consent flow</li>
                </ol>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-3">The "Google Account" block above it is the separate sign-in connection. Connecting there does not connect a calendar, and you can use either one without the other.</p>
            </div>

            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">2. Choose a Calendar and Sync Direction</h4>
                <ol class="doc-list doc-list-numbered text-sm">
                    <li>Edit the schedule and open the Integrations section</li>
                    <li>Select the "Google Calendar" tab</li>
                    <li>Pick the calendar to sync with under "Select Google Calendar"</li>
                    <li>Choose a sync direction: To Google Calendar, From Google Calendar, Bidirectional Sync or No Sync</li>
                    <li>Save the schedule. Saving is what applies the selection and sets up the change channel</li>
                    <li>Turning sync on does not push the events you already have. Use step 5 or step 6 for those</li>
                </ol>
            </div>

            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">3. Set the Deletion Policy</h4>
                <ol class="doc-list doc-list-numbered text-sm">
                    <li>With From Google Calendar or Bidirectional Sync selected, the "When an event is deleted in the connected calendar" options appear</li>
                    <li>Choose Keep it here, Mark as cancelled, or Delete it here. Keep it here is the default</li>
                    <li>Save the schedule</li>
                </ol>
            </div>

            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">4. Sync a Single Event</h4>
                <ol class="doc-list doc-list-numbered text-sm">
                    <li>Open any event's edit page</li>
                    <li>Go to the "Google Calendar" section. It appears once the schedule has a calendar selected and pushes to Google</li>
                    <li>Click "Sync to Google Calendar", or "Remove from Google Calendar" to take the copy back off the calendar</li>
                </ol>
            </div>

            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">5. Sync the Whole Schedule</h4>
                <ol class="doc-list doc-list-numbered text-sm">
                    <li>Open the schedule and use the "Actions" menu. "Sync Events" is listed once your Google account is connected and the schedule has a calendar selected</li>
                    <li>Choose "Sync Events" to run the schedule's saved sync direction now. A schedule left on No Sync is pushed to Google instead, and To Google Calendar is then saved as its direction</li>
                    <li>A push only creates events that are missing from the calendar; it does not rewrite copies that are already there</li>
                    <li>An inbound direction also imports from the calendar, exactly as the scheduled poll would</li>
                </ol>
            </div>

            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">6. Resync Everything to Google</h4>
                <ol class="doc-list doc-list-numbered text-sm">
                    <li>Use this after switching Google accounts or target calendars. Save the schedule first, because the resync pushes to the saved calendar. The button stays disabled while the dropdown differs from the saved calendar</li>
                    <li>On the schedule's Google Calendar tab, click "Resync to Google Calendar". Only the schedule owner sees this button, and the request is refused unless the direction includes To Google Calendar</li>
                    <li>Events already sitting on the saved calendar are left alone. An event whose copy is on a different calendar has that old copy deleted and a fresh one created, so switching calendars does not leave duplicates behind</li>
                    <li>It only ever pushes to Google and never imports, and it only covers published events</li>
                    <li>The resync runs in the background in batches and can take a few minutes on a large schedule, so it needs a queue worker. Clicking it again is safe: it picks up only the work still outstanding</li>
                </ol>
            </div>

            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">7. Mirror a Schedule into Your Own Calendar</h4>
                <ol class="doc-list doc-list-numbered text-sm">
                    <li>As a team member who does not own the schedule, connect your own Google Calendar first</li>
                    <li>Open the schedule's Integrations &gt; Google Calendar tab and find "Sync to My Calendar"</li>
                    <li>Pick one of your calendars under "Select Your Calendar" and click Save. That block saves on its own, so you do not need to save the schedule</li>
                    <li>Choosing "No Sync" removes the events again</li>
                </ol>
            </div>
        </div>

        <h3 class="doc-subheading">Automatic Sync</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once a schedule pushes to Google, its events are synced automatically when they are:</p>
        <ul class="doc-list mb-6">
            <li>Published, whether that is a new event or a draft you just published</li>
            <li>Edited, which updates the existing Google entry in place</li>
            <li>Deleted, cancelled or turned back into a draft, which removes the copy from the calendar. Restoring a cancelled event puts it back</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Drafts are never pushed, so an event only reaches the calendar once it is published. Members with personal calendar sync receive the same create, update and delete operations even when the owner has not turned on sync for the schedule.</p>

        <h3 class="doc-subheading">For Developers</h3>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Sync Helpers on the Event Model</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Outbound sync and status checks go through the <code class="doc-inline-code">Event</code> model:</p>

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

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-3 mt-6">How the Work Is Dispatched</h4>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The mapping between an event and its Google copy lives in the <code class="doc-inline-code">calendar_syncs</code> table, one row per user, event and schedule, together with the calendar the copy was created on.</p>
        <ul class="doc-list mb-6">
            <li><code class="doc-inline-code">SyncEventToGoogleCalendar</code> performs one create, update or delete. Saving an event runs it inline, so the calendar is up to date by the time the save finishes and a queue worker is not required</li>
            <li><code class="doc-inline-code">ForceResyncGoogleCalendar</code> backs the "Resync to Google Calendar" button and is queued. It handles a small batch of events per run and dispatches a follow-up while any remain, so a large schedule finishes across several runs instead of timing out</li>
            <li>Inbound sync is serialized per schedule with a lock, so the webhook and the 15-minute poll cannot import the same event twice</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Queue worker</div>
            <p>Everyday sync does not need a queue worker. Run one (<code class="doc-inline-code">php artisan queue:work</code>) if you want the bulk resync, since that job is queued and will otherwise sit unprocessed.</p>
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

        <p class="text-gray-600 dark:text-gray-300 mb-4">These are the application's own session-authenticated routes, not part of the public REST API. Every route except the two webhook routes requires a signed-in user with a verified email address. The webhook routes are public, and are authenticated by the channel token instead.</p>

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
                        <td>Start the OAuth flow</td>
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
                        <td><code class="doc-inline-code">GET /google-calendar/disconnect</code></td>
                        <td>Disconnect Google Calendar, remove change channels and clear sync records</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GET /google-calendar/calendars</code></td>
                        <td>Get the connected account's calendars</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /google-calendar/sync/{subdomain}</code></td>
                        <td>Sync a schedule in the direction given by <code class="doc-inline-code">sync_direction</code> (<code class="doc-inline-code">to</code>, <code class="doc-inline-code">from</code> or <code class="doc-inline-code">both</code>), and optionally save that direction and the deletion policy</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /google-calendar/force-sync-to-google/{subdomain}</code></td>
                        <td>Queue a full push of a schedule to Google. Owner only, and rate limited to 5 requests per minute</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /google-calendar/member-sync/{subdomain}</code></td>
                        <td>Turn a member's personal calendar sync on or off</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /google-calendar/sync-event/{subdomain}/{eventId}</code></td>
                        <td>Sync one event</td>
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

        <h3 class="doc-subheading">Scheduled Commands</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">These Artisan commands keep inbound sync and the change channels healthy:</p>
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
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Troubleshooting
        </h2>

        <h3 class="doc-subheading">Common Issues</h3>

        <div class="space-y-4 mb-8">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Google Calendar not connected"</h4>
                <ul class="doc-list text-sm">
                    <li>Connect the Google account first, from Settings &gt; Google Settings</li>
                    <li>Connecting a Google account for sign-in is not the same thing; the calendar block has its own Connect button</li>
                    <li>Check that <code class="doc-inline-code">GOOGLE_CLIENT_ID</code>, <code class="doc-inline-code">GOOGLE_CLIENT_SECRET</code> and <code class="doc-inline-code">GOOGLE_REDIRECT_URI</code> are set and that the redirect URI matches the one registered in Google Cloud Console</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Repeated re-authorization, or "token refresh failed"</h4>
                <ul class="doc-list text-sm">
                    <li>This means no refresh token was stored. Reconnect the account, which forces the consent prompt again</li>
                    <li>Revoking access to the app in the Google Account security settings and connecting again clears a stuck grant</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Events do not appear in Google Calendar</h4>
                <ul class="doc-list text-sm">
                    <li>The schedule's sync direction has to be To Google Calendar or Bidirectional Sync, and the schedule has to be saved after the change</li>
                    <li>Events that already existed when you turned sync on are not pushed in bulk. Use "Sync Events" from the schedule's Actions menu, the per-event button, or "Resync to Google Calendar"</li>
                    <li>Draft events are never pushed. Publish the event first</li>
                    <li>Confirm the Google Calendar API is enabled on the project and that the account granted the calendar scopes</li>
                    <li>Check the logs for the failing call</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">The "Google Calendar" section is missing on the event edit page</h4>
                <ul class="doc-list text-sm">
                    <li>It only appears when the schedule has a calendar selected and pushes to Google</li>
                    <li>Select a calendar on Integrations &gt; Google Calendar and save the schedule</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Google Calendar changes do not reach Event Schedule</h4>
                <ul class="doc-list text-sm">
                    <li>Inbound sync needs From Google Calendar or Bidirectional Sync</li>
                    <li>For real-time notifications, the app needs a public HTTPS URL on a domain verified with Google, and <code class="doc-inline-code">GOOGLE_WEBHOOK_SECRET</code> must be set; notifications with a mismatched token are rejected. When Google refuses the channel the save still succeeds and the error is only visible in the log</li>
                    <li>Without a public URL, rely on the 15-minute <code class="doc-inline-code">google:sync</code> poll and confirm the scheduler cron is running</li>
                    <li>Run <code class="doc-inline-code">php artisan google:sync --role=</code> with the schedule id to test a single schedule by hand</li>
                    <li>Inbound sync runs on the schedule owner's Google account, so it stops if the owner disconnects even when other members are still connected</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Resync to Google Calendar" is greyed out or refused</h4>
                <ul class="doc-list text-sm">
                    <li>The button is disabled while the calendar dropdown differs from the saved calendar. Save the schedule first</li>
                    <li>Only the schedule owner sees the button, and the server refuses the request unless the direction includes To Google Calendar</li>
                    <li>The job is queued, so it needs a queue worker to run</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Inbound sync stopped after a while</h4>
                <ul class="doc-list text-sm">
                    <li>Change channels expire. The daily <code class="doc-inline-code">google:refresh-webhooks</code> command renews them, so make sure the scheduler is running</li>
                    <li><code class="doc-inline-code">php artisan google:refresh-webhooks --force</code> rebuilds them immediately</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Leftover events after switching calendars or accounts</h4>
                <ul class="doc-list text-sm">
                    <li>Save the schedule with the new calendar selected, then run "Resync to Google Calendar" so old copies are removed and fresh ones are created</li>
                    <li>Events synced before Event Schedule started recording which calendar each copy lived on cannot be cleaned up automatically. Delete those few leftovers in Google Calendar by hand</li>
                    <li>Nothing is removed from the calendar of an account that has already been disconnected, because the app no longer holds a token for it</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Deleting in Google removed nothing here, or removed too much</h4>
                <ul class="doc-list text-sm">
                    <li>The deletion policy defaults to Keep it here, which is why nothing changes locally</li>
                    <li>Deletions arrive only through the incremental sync, so the schedule must have completed at least one inbound sync first</li>
                </ul>
            </div>
        </div>

        <h3 class="doc-subheading">Logs</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Sync operations are logged in the application logs. Check <code class="doc-inline-code">storage/logs/laravel.log</code> for detailed information about sync operations, and <code class="doc-inline-code">storage/logs/scheduler.log</code> for the scheduled sync and channel-renewal runs.</p>
    </section>

    <!-- Security Considerations -->
    <section id="security" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            Security Considerations
        </h2>
        <ol class="doc-list doc-list-numbered">
            <li><span class="font-semibold text-gray-900 dark:text-white">Token Storage:</span> Google access and refresh tokens are stored per user and encrypted at rest with the install's <code class="doc-inline-code">APP_KEY</code>, and they are hidden from the model's serialized output</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Scope Limitation:</span> Only the calendar scopes the integration needs are requested, plus <code class="doc-inline-code">openid</code>, <code class="doc-inline-code">email</code> and <code class="doc-inline-code">profile</code> to identify the account</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">OAuth State Check:</span> The connect flow carries a random state value that is verified on the callback, so a forged callback is rejected</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Webhook Authentication:</span> <code class="doc-inline-code">GOOGLE_WEBHOOK_SECRET</code> is the channel token Google echoes back, and mismatched notifications are rejected. Both webhook routes are rate limited</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">User Authorization:</span> Users can only sync events belonging to schedules they are a member of, and only the schedule owner can trigger a full resync</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Token Refresh:</span> Access tokens are refreshed automatically before each call that needs one</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Clean Disconnect:</span> Disconnecting removes the change channels and turns sync off for the schedules the user owns, clears their sync cursors, and deletes the stored tokens, the user's event mappings and their calendar selection on every schedule they belong to. It does not delete anything already on the Google calendar</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Audit Trail:</span> Connecting, disconnecting, syncing a schedule and toggling personal member sync are all written to the audit log</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Secrets in .env:</span> Keep the client secret and webhook secret in <code class="doc-inline-code">.env</code>, and never commit them to source control</li>
        </ol>
    </section>
</x-docs-page>
