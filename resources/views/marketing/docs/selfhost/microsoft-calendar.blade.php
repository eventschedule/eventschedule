<x-docs-page
    key="selfhost/microsoft-calendar"
    title="Outlook and Microsoft 365 Sync for Selfhost - Event Schedule"
    description="Set up two-way Outlook and Microsoft 365 calendar sync on a selfhosted Event Schedule install through Microsoft Graph, with optional Teams meeting links."
    lede="Set up and use the Microsoft 365 / Outlook Calendar integration for bidirectional sync between Event Schedule and Outlook through Microsoft Graph."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#prerequisites">Prerequisites</x-doc-nav-link>
        <x-doc-nav-group label="Setup Instructions" href="#setup">
            <x-doc-nav-link href="#setup-registration">1. App registration</x-doc-nav-link>
            <x-doc-nav-link href="#setup-permissions">2. API permissions</x-doc-nav-link>
            <x-doc-nav-link href="#setup-secret">3. Client secret and ID</x-doc-nav-link>
            <x-doc-nav-link href="#setup-env">4. Environment</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Features" href="#features">
            <x-doc-nav-link href="#owner-only">The owner's account only</x-doc-nav-link>
            <x-doc-nav-link href="#sync-direction">Sync direction</x-doc-nav-link>
            <x-doc-nav-link href="#what-is-synced">What is synced</x-doc-nav-link>
            <x-doc-nav-link href="#teams">Teams meeting links</x-doc-nav-link>
            <x-doc-nav-link href="#inbound-events">Events from Outlook</x-doc-nav-link>
            <x-doc-nav-link href="#delete-sync">Deletions in Outlook</x-doc-nav-link>
            <x-doc-nav-link href="#calendar-sync-tab">An event's Calendar sync tab</x-doc-nav-link>
            <x-doc-nav-link href="#realtime">Real-time and polling</x-doc-nav-link>
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
        <p>This page is for whoever runs the install. Outlook Calendar sync needs one app registration in Microsoft Entra ID and five environment variables, two of them optional. Once they are set, a schedule's owner can connect their Microsoft account and keep the schedule and one Outlook calendar in step, in one direction or both, through Microsoft Graph. Nothing is configured per schedule on the server.</p>

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
                        <td><strong>Settings</strong> &rarr; <strong>Integrations</strong> &rarr; <strong>Outlook Calendar</strong></td>
                        <td>A user connects their own Microsoft account here with <strong>Connect Outlook Calendar</strong>, and later disconnects it. Only a schedule owner's connection is ever used for sync</td>
                    </tr>
                    <tr>
                        <td><strong>Edit Schedule</strong> &rarr; <strong>Integrations</strong> &rarr; <strong>Outlook Calendar</strong> row</td>
                        <td>The schedule's owner picks the calendar, the sync direction and what a deletion in Outlook does here, and can turn on <strong>Create Teams meetings for online events</strong></td>
                    </tr>
                    <tr>
                        <td>Event form &rarr; <strong>Calendar sync</strong> tab</td>
                        <td>Shows whether this event has a copy in Outlook. <strong>Sync Now</strong> and <strong>Remove</strong> are the owner's to use</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <x-doc-screenshot id="creating-schedules--section-integrations" alt="The Integrations tab of the schedule form, with Google Calendar, Outlook Calendar, CalDAV Calendar and Calendar text and feeds as rows that open in place" />

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Included on every plan</div>
            <p>Outlook Calendar sync is a free-tier feature, so no plan gate applies. Once the server credentials below are in place, every schedule on the install can use it.</p>
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
            <li>A Microsoft Entra ID (Azure AD) tenant or an Azure account to register an application</li>
            <li>Access to the Azure Portal to create an app registration</li>
            <li>A redirect URI that matches your app registration exactly, so users can complete the OAuth sign-in</li>
            <li>The Laravel scheduler cron from <a href="{{ route('marketing.docs.selfhost.installation') }}#cron" class="doc-link">Installation</a>, which drives the inbound poll, the subscription renewals and the queue</li>
            <li>For near-real-time inbound sync, a publicly reachable HTTPS URL, because Microsoft Graph must be able to call the webhook endpoint on your server</li>
            <li>For near-real-time inbound sync, <code class="doc-inline-code">QUEUE_CONNECTION=database</code></li>
        </ul>

        <p>The queue matters because inbound sync is dispatched to it, so that Microsoft Graph gets a fast response. On <code class="doc-inline-code">database</code>, the scheduler's minutely queue worker runs the sync within about a minute; keep <code class="doc-inline-code">php artisan queue:work</code> running as well only if you want it sooner. On the shipped <code class="doc-inline-code">sync</code> connection the sync runs inside the webhook request, which can be slow enough that Graph deprovisions the subscription. Either way, the 15-minute poll catches anything a notification missed.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">No public URL?</div>
            <p>Installs without a public HTTPS URL still work. Instead of near-real-time webhooks, inbound changes are picked up by the 15-minute <code class="doc-inline-code">microsoft:sync</code> polling fallback.</p>
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

        <h3 id="setup-registration" class="doc-subheading">1. Azure App Registration</h3>
        <ol class="doc-list doc-list-numbered">
            <li>Go to the <a href="https://portal.azure.com/" target="_blank" rel="noopener noreferrer" class="doc-link">Azure Portal</a> and open <strong>Microsoft Entra ID</strong> &rarr; <strong>App registrations</strong> &rarr; <strong>New registration</strong></li>
            <li>Enter a name for the application (for example, "Event Schedule")</li>
            <li>Under <strong>Supported account types</strong>, choose "Accounts in any organizational directory and personal Microsoft accounts" (this matches <code class="doc-inline-code">MICROSOFT_TENANT=common</code>)</li>
            <li>Under <strong>Redirect URI</strong>, select the <strong>Web</strong> platform and enter: <code class="doc-inline-code">{APP_URL}/microsoft-calendar/callback</code></li>
            <li>Click <strong>Register</strong></li>
        </ol>

        <h3 id="setup-permissions" class="doc-subheading">2. API Permissions</h3>
        <p>Event Schedule requests delegated permissions only, so it acts as the signed-in user and never gains tenant-wide calendar access.</p>
        <ol class="doc-list doc-list-numbered">
            <li>In the app registration, open <strong>API permissions</strong> &rarr; <strong>Add a permission</strong> &rarr; <strong>Microsoft Graph</strong> &rarr; <strong>Delegated permissions</strong></li>
            <li>Add these five delegated permissions: <code class="doc-inline-code">Calendars.ReadWrite</code>, <code class="doc-inline-code">offline_access</code>, <code class="doc-inline-code">openid</code>, <code class="doc-inline-code">email</code> and <code class="doc-inline-code">profile</code></li>
            <li>If your tenant requires it, grant admin consent for the permissions</li>
        </ol>
        <p>The scope list is fixed: Event Schedule always requests exactly these five. <code class="doc-inline-code">offline_access</code> is the one that yields a refresh token, so without it users are pushed back through sign-in as soon as the access token expires.</p>

        <h3 id="setup-secret" class="doc-subheading">3. Client Secret and Client ID</h3>
        <ol class="doc-list doc-list-numbered">
            <li>Open <strong>Certificates &amp; secrets</strong> &rarr; <strong>New client secret</strong>, then copy the secret <strong>Value</strong> immediately (it is only shown once)</li>
            <li>Open the <strong>Overview</strong> page and copy the <strong>Application (client) ID</strong></li>
        </ol>

        <h3 id="setup-env" class="doc-subheading">4. Environment Configuration</h3>
        <p>Add the following environment variables to your <code class="doc-inline-code">.env</code> file. <code class="doc-inline-code">.env.example</code> carries the same five, commented out, in its "Outlook / Microsoft 365 calendar sync (optional)" block:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">MICROSOFT_CLIENT_ID</span>=<span class="code-string">your_application_client_id</span>
<span class="code-variable">MICROSOFT_CLIENT_SECRET</span>=<span class="code-string">your_client_secret_value</span>
<span class="code-variable">MICROSOFT_REDIRECT_URI</span>=<span class="code-string">https://your-domain.com/microsoft-calendar/callback</span>
<span class="code-variable">MICROSOFT_TENANT</span>=<span class="code-string">common</span>
<span class="code-variable">MICROSOFT_WEBHOOK_SECRET</span>=<span class="code-string">a_long_random_string</span></code></pre>
        </div>

        <p>If you cache your configuration, run <code class="doc-inline-code">php artisan config:cache</code> after editing <code class="doc-inline-code">.env</code>, or the new values will not be picked up.</p>

        <h3 id="variable-reference" class="doc-subheading">Variable Reference</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Required</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">MICROSOFT_CLIENT_ID</code></td>
                        <td>Yes</td>
                        <td>The Application (client) ID from the app registration Overview page. Until it is set, the Settings page shows "Outlook Calendar is not configured on this server" instead of a connect button</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">MICROSOFT_CLIENT_SECRET</code></td>
                        <td>Yes</td>
                        <td>The client secret Value created under Certificates &amp; secrets</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">MICROSOFT_REDIRECT_URI</code></td>
                        <td>Yes</td>
                        <td>Must exactly match the redirect URI registered in Azure (<code class="doc-inline-code">{APP_URL}/microsoft-calendar/callback</code>). It is sent on both the authorization request and the token exchange</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">MICROSOFT_TENANT</code></td>
                        <td>No, defaults to <code class="doc-inline-code">common</code></td>
                        <td>Use <code class="doc-inline-code">common</code> for multi-tenant plus personal accounts, or your specific tenant id for a single-tenant app</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">MICROSOFT_WEBHOOK_SECRET</code></td>
                        <td>Only for webhooks</td>
                        <td>Any long random string. It is the <code class="doc-inline-code">clientState</code> that authenticates inbound Microsoft Graph notifications. Not needed for polling-only installs</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p>The webhook secret is the only authenticity check on a notification. Graph subscriptions cannot be created at all while <code class="doc-inline-code">MICROSOFT_WEBHOOK_SECRET</code> is empty: the attempt is refused rather than made without a <code class="doc-inline-code">clientState</code>. Once set, Graph echoes the value back on every change notification, and Event Schedule ignores any notification whose value does not match, answering <code class="doc-inline-code">401</code> when none of them do.</p>
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
                        <td>A user connects their Microsoft account through OAuth under <strong>Settings</strong> &rarr; <strong>Integrations</strong> &rarr; <strong>Outlook Calendar</strong>. The tokens are stored on that user record</td>
                    </tr>
                    <tr>
                        <td>Pick a calendar and a direction</td>
                        <td>The schedule's owner picks one Outlook calendar and a sync direction in the <strong>Outlook Calendar</strong> row of <strong>Edit Schedule</strong> &rarr; <strong>Integrations</strong></td>
                    </tr>
                    <tr>
                        <td>Outbound</td>
                        <td>Publishing, editing or deleting an event pushes the change to the selected calendar</td>
                    </tr>
                    <tr>
                        <td>Inbound</td>
                        <td>Microsoft Graph subscriptions notify the webhook endpoint, and a queued job pulls the changes in with a Graph delta query</td>
                    </tr>
                    <tr>
                        <td>Polling fallback</td>
                        <td>A 15-minute <code class="doc-inline-code">microsoft:sync</code> command catches anything webhooks miss, and is the only inbound path on installs without a public URL</td>
                    </tr>
                    <tr>
                        <td>Subscription renewal</td>
                        <td>A daily <code class="doc-inline-code">microsoft:refresh-webhooks</code> command renews Graph subscriptions, which are created with a 60-hour (about 2.5 day) expiry</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="owner-only" class="doc-subheading">Outlook Sync Uses the Schedule Owner's Account Only</h3>
        <p>The calendar selection, the Graph subscription, and every outbound and inbound sync run on the schedule owner's connected Microsoft account. Team members do not get their own Outlook sync, and the <strong>Outlook Calendar</strong> row only shows the settings to the owner: anyone else, and an owner who has not connected yet, sees a <strong>Connect Outlook Calendar</strong> button there instead. Per-member calendar sync is a <a href="{{ route('marketing.docs.selfhost.google_calendar') }}#member-sync" class="doc-link">Google Calendar feature</a>.</p>

        <h3 id="sync-direction" class="doc-subheading">Sync Direction</h3>
        <p>Each schedule picks one of four options under <strong>Sync Direction</strong>. Saving the schedule creates or removes the Graph subscription for you.</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Option</th>
                        <th>What it does</th>
                        <th>Graph subscription</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>To Outlook Calendar</td>
                        <td>Published Event Schedule events are pushed to the selected Outlook calendar</td>
                        <td>Not created</td>
                    </tr>
                    <tr>
                        <td>From Outlook Calendar</td>
                        <td>Outlook events are imported into Event Schedule</td>
                        <td>Created</td>
                    </tr>
                    <tr>
                        <td>Bidirectional Sync</td>
                        <td>Both of the above: new events and edits flow in each direction. Deleting an event here removes its Outlook copy; deleting one in Outlook follows <a href="#delete-sync" class="doc-link">the deletion setting</a></td>
                        <td>Created</td>
                    </tr>
                    <tr>
                        <td>No Sync</td>
                        <td>Outlook sync is turned off for this schedule. Events already in Outlook are left alone</td>
                        <td>Removed</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="what-is-synced" class="doc-subheading">Event Information Synced</h3>
        <ul class="doc-list">
            <li>Event name, as the Outlook subject</li>
            <li>Description. By default the event description is sent as plain text; if the schedule has a <strong>Calendar Description Template</strong> set under <strong>Integrations</strong> &rarr; <strong>Calendar text and feeds</strong>, the rendered template is sent instead (see the <a href="{{ route('marketing.docs.creating_schedules') }}#available-variables" class="doc-link">available variables</a>). On an update with no template and an empty description, no body is sent at all, so notes you typed on the Outlook copy survive</li>
            <li>Start time in the schedule's timezone, and an end time calculated from the event duration: two hours when no duration is set, and an all-day entry when the duration is 0, which is how a one-day all-day Outlook entry is stored when it arrives here</li>
            <li>Location, taken from the venue's best available address</li>
            <li>Privacy: an unlisted event is marked <strong>Private</strong> in Outlook, everything else is normal</li>
            <li>A Microsoft Teams join link, for online events when the toggle is enabled</li>
        </ul>

        <h3 id="not-sent" class="doc-subheading">What Is Not Sent</h3>
        <ul class="doc-list">
            <li><strong>Drafts.</strong> A Draft or Internal event is never pushed by a save, so an event first appears in Outlook when you publish it</li>
            <li><strong>Recurrence.</strong> Recurring events are sent as a single Outlook entry on the series start date rather than as an Outlook recurrence. Use the schedule's <a href="{{ route('marketing.docs.sharing') }}#calendar-feeds" class="doc-link">iCal feed</a> when you need every date of a series in a calendar app</li>
        </ul>

        <h3 id="teams" class="doc-subheading">Microsoft Teams Meeting Links</h3>
        <p>Enable <strong>Create Teams meetings for online events</strong> in the schedule's <strong>Outlook Calendar</strong> row. Every event with no venue is then created in Outlook as a Teams for Business meeting, and the join link is written into the event's <strong>Event URL</strong> field, but only when that field is still empty so a link you entered yourself is never overwritten.</p>
        <p>Personal Microsoft accounts usually cannot create Teams for Business meetings. When Graph rejects the request, Event Schedule retries immediately without the Teams flags, so you get a normal Outlook event and no join link rather than a failed sync.</p>

        <h3 id="inbound-events" class="doc-subheading">Events That Arrive From Outlook</h3>
        <p>Inbound sync uses a Graph delta query over a window running from 30 days ago to 365 days ahead. On the first run, or after you switch calendars, the whole window is read; after that only changes are fetched. Events brought in this way:</p>
        <ul class="doc-list">
            <li>Arrive already approved and use the schedule's slug pattern and default category</li>
            <li>Take their name, description, start time and duration from the Outlook entry. The description is converted from HTML to Markdown</li>
            <li>Convert the Outlook location into a venue, reusing one of your existing venues when the name or address matches. An event that already has a venue keeps it</li>
            <li>Are matched to an existing event by name and start time when there is no stored mapping yet, so an event you pushed out does not come back as a duplicate</li>
            <li>Never overwrite an appointment booking. An event created by a booking is owned by Event Schedule: inbound sync does not rewrite its name, description or time, so moving the Outlook copy will not move a customer's booking</li>
        </ul>

        <h3 id="delete-sync" class="doc-subheading">When an Event Is Deleted in Outlook</h3>
        <p>Schedules that import from Outlook also choose <strong>When an event is deleted in the connected calendar</strong>, under the sync direction. It is one setting for the schedule, shared with the Google Calendar integration, and it is shown in one place only: if you have also connected Google Calendar, it appears in the <strong>Google Calendar</strong> row instead of this one, and only while that row's own direction is From Google Calendar or Bidirectional Sync.</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Option</th>
                        <th>Result in Event Schedule</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Keep it here</td>
                        <td>The event stays exactly as it is. This is the default</td>
                    </tr>
                    <tr>
                        <td>Mark as cancelled</td>
                        <td>The event is marked cancelled rather than removed, which is reversible. Recommended when tickets are sold</td>
                    </tr>
                    <tr>
                        <td>Delete it here</td>
                        <td>The event is removed, which cannot be undone. Events with ticket sales or ad boost spend are marked cancelled instead of deleted</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p>Only a real deletion in Outlook triggers this policy. An event that merely moves outside the sync window is left untouched. Marking an event cancelled this way does not notify anyone: the cancellation notice that <strong>Cancel event</strong> in the event editor can send to ticket buyers and to the event's interest list only goes out from there.</p>
        <p>An event that belongs to more than one schedule is only detached from this schedule, never cancelled or deleted outright, unless this schedule is the one that owns it. The other schedules keep the event as it is.</p>

        <h3 id="calendar-sync-tab" class="doc-subheading">An Event's Calendar Sync Tab</h3>
        <p>Once a schedule has a calendar selected and a direction that includes To Outlook Calendar, the form of a saved event gains a <strong>Calendar sync</strong> tab with an <strong>Outlook Calendar</strong> row:</p>
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
                        <td>The event has a copy in the owner's Outlook calendar. <strong>Remove</strong> deletes the Outlook copy, after a confirmation</td>
                    </tr>
                    <tr>
                        <td>Not synced</td>
                        <td>No copy yet. The line under it says why: "It is copied the next time you save." for a published event, and "Only an event guests can see is copied to the calendar." for a Draft or Internal one. <strong>Sync Now</strong> creates the copy at once</td>
                    </tr>
                    <tr>
                        <td>Tab absent</td>
                        <td>The event has not been saved yet, the schedule has no calendar selected, or its direction does not include To Outlook Calendar</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p>The status is the schedule owner's copy, while <strong>Sync Now</strong> and <strong>Remove</strong> act with the Microsoft account of whoever is signed in, so they are the owner's buttons to use. If a request fails, the reason is shown in the row.</p>

        <h3 id="realtime" class="doc-subheading">Real-Time Sync and Polling Fallback</h3>
        <ul class="doc-list">
            <li>Graph sends one notification per changed event; Event Schedule collapses them to at most one inbound sync per schedule per minute, then reads every pending change in a single delta request</li>
            <li>The 15-minute <code class="doc-inline-code">microsoft:sync</code> command polls the schedules whose direction is <strong>From Outlook Calendar</strong> or <strong>Bidirectional Sync</strong>, and is the primary path when no public URL is available</li>
            <li>The daily <code class="doc-inline-code">microsoft:refresh-webhooks</code> command extends any subscription due to expire within the next day, and recreates any that Graph has already dropped</li>
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

        <h3 id="step-by-step" class="doc-subheading">Step by Step</h3>
        <p>Steps 1 and 2 turn sync on, and are done by the schedule's owner.</p>

        <ol class="doc-steps">
            <li class="doc-step">
                <h4 class="doc-step-title">Connect Outlook Calendar</h4>
                <ol class="doc-list doc-list-numbered">
                    <li>Open <strong>Settings</strong> in the sidebar and choose the <strong>Integrations</strong> tab</li>
                    <li>In <strong>Outlook Calendar</strong>, click <strong>Connect Outlook Calendar</strong></li>
                    <li>Authorize the application in the Microsoft sign-in flow. You come back to the same section, which now reads <strong>Outlook Calendar Connected</strong></li>
                </ol>
                <p>If the section shows "Outlook Calendar is not configured on this server" instead of the button, <code class="doc-inline-code">MICROSOFT_CLIENT_ID</code> is still missing from <code class="doc-inline-code">.env</code>. Connecting is half of it: nothing syncs until a schedule is told to.</p>
            </li>

            <li class="doc-step">
                <h4 class="doc-step-title">Choose a Calendar and Sync Direction</h4>
                <ol class="doc-list doc-list-numbered">
                    <li>Open the schedule, click <strong>Edit Schedule</strong>, choose the <strong>Integrations</strong> tab and open the <strong>Outlook Calendar</strong> row</li>
                    <li>Pick which Outlook calendar to sync with under <strong>Select Outlook Calendar</strong></li>
                    <li>Choose the <strong>Sync Direction</strong>: To Outlook Calendar, From Outlook Calendar, Bidirectional Sync, or No Sync</li>
                    <li>Click <strong>Save</strong></li>
                </ol>
                <p>Only the schedule owner sees these controls.</p>
            </li>

            <li class="doc-step">
                <h4 class="doc-step-title">Push Events to Outlook</h4>
                <p>Create and publish events as usual. Every event published or edited from then on is pushed to the selected calendar automatically. Events that already existed when you turned sync on are not pushed in bulk, so add them one at a time with the next step.</p>
            </li>

            <li class="doc-step">
                <h4 class="doc-step-title">Sync a Single Event by Hand</h4>
                <p>Open the event's form and choose the <strong>Calendar sync</strong> tab. In the <strong>Outlook Calendar</strong> row, click <strong>Sync Now</strong>, or <strong>Remove</strong> to delete the Outlook copy. The tab only appears once a calendar is selected and the direction includes To Outlook Calendar.</p>
            </li>

            <li class="doc-step">
                <h4 class="doc-step-title">Enable Teams Meeting Links</h4>
                <p>In the schedule's <strong>Outlook Calendar</strong> row, turn on <strong>Create Teams meetings for online events</strong> and save. Events with no venue are created as Teams meetings, and the join link is saved to the event when it has no link yet.</p>
            </li>
        </ol>

        <h3 id="automatic-sync" class="doc-subheading">Automatic Sync</h3>
        <p>Once the schedule owner has connected Outlook and the direction includes <strong>To Outlook Calendar</strong>, events are synced automatically when they are:</p>
        <ul class="doc-list">
            <li>Published, whether that is a new event or a draft you just published</li>
            <li>Edited, which updates the existing Outlook entry in place</li>
            <li>Deleted, cancelled or un-published, which removes the Outlook copy. Restoring a cancelled event puts it back</li>
        </ul>
        <p>Inbound polling and subscription renewal run through the <a href="#scheduled-commands" class="doc-link">scheduled commands</a> below, so the Laravel scheduler cron has to be active.</p>
    </section>

    <!-- API Endpoints -->
    <section id="api-endpoints" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
            </svg>
            API Endpoints
        </h2>

        <p>These are the application's own session-authenticated routes, not part of the public REST API. "Signed in" means a signed-in user with a verified email address.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Endpoint</th>
                        <th>Access</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">GET /microsoft-calendar/redirect</code></td>
                        <td>Signed in</td>
                        <td>Start OAuth flow</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GET /microsoft-calendar/callback</code></td>
                        <td>Signed in</td>
                        <td>OAuth callback</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GET /microsoft-calendar/reauthorize</code></td>
                        <td>Signed in</td>
                        <td>Re-run consent to obtain a refresh token</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GET /microsoft-calendar/disconnect</code></td>
                        <td>Signed in</td>
                        <td>Disconnect Outlook Calendar. Deletes the Graph subscriptions on the schedules you own, clears their sync direction and calendar selection, drops the stored event mappings, and clears the tokens</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GET /microsoft-calendar/calendars</code></td>
                        <td>Signed in</td>
                        <td>Get the user's calendars as JSON. This is what fills the calendar dropdown</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /microsoft-calendar/sync/{subdomain}</code></td>
                        <td>Signed in</td>
                        <td>Sync a whole schedule with the caller's own Microsoft connection, so it is the schedule owner's to call. Send <code class="doc-inline-code">sync_direction</code> as <code class="doc-inline-code">to</code>, <code class="doc-inline-code">from</code> or <code class="doc-inline-code">both</code>, which also saves it as the schedule's direction, or omit it to use the saved one. There is no button for this, so it is the way to backfill events that predate the connection</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /microsoft-calendar/sync-event/{subdomain}/{eventId}</code></td>
                        <td>Signed in</td>
                        <td>Sync a specific event, with the caller's own Microsoft connection. This is what <strong>Sync Now</strong> calls</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">DELETE /microsoft-calendar/unsync-event/{subdomain}/{eventId}</code></td>
                        <td>Signed in</td>
                        <td>Remove the event's Outlook copy and drop its mapping. This is what <strong>Remove</strong> calls</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GET /microsoft-calendar/webhook</code></td>
                        <td>Public, rate limited to 10 requests per minute</td>
                        <td>Microsoft Graph subscription validation handshake</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /microsoft-calendar/webhook</code></td>
                        <td>Public, rate limited to 60 requests per minute, <code class="doc-inline-code">clientState</code></td>
                        <td>Microsoft Graph change notifications (also handles the validation handshake)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="scheduled-commands" class="doc-subheading">Scheduled Commands</h3>
        <p>These Artisan commands keep inbound sync and Graph subscriptions healthy:</p>
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
                        <td><code class="doc-inline-code">microsoft:sync</code></td>
                        <td>Every 15 minutes</td>
                        <td>Polls Outlook for changes (inbound sync fallback). Add <code class="doc-inline-code">--role=</code> with a schedule id to poll just one schedule</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">microsoft:refresh-webhooks</code></td>
                        <td>Daily</td>
                        <td>Renews any Microsoft Graph subscription due to expire within the next day. Add <code class="doc-inline-code">--role=</code> with a schedule id or subdomain to target one schedule, or <code class="doc-inline-code">--force</code> to renew them all</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p>These commands run through the Laravel scheduler, which requires the following cron entry:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>crontab</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>* * * * * php artisan schedule:run</code></pre>
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
            <x-doc-faq question='"Outlook Calendar is not configured on this server"'>
                <ul class="doc-list">
                    <li><code class="doc-inline-code">MICROSOFT_CLIENT_ID</code> is not set, so <strong>Settings</strong> shows this notice in place of the connect button</li>
                    <li>If the variable is in <code class="doc-inline-code">.env</code> and the notice stays, the configuration is cached: run <code class="doc-inline-code">php artisan config:cache</code> again</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="No refresh token, or repeated re-authentication">
                <ul class="doc-list">
                    <li>Make sure the <code class="doc-inline-code">offline_access</code> scope is granted in the app registration</li>
                    <li>Visit <code class="doc-inline-code">/microsoft-calendar/reauthorize</code> to force a fresh consent prompt, which is what returns a new refresh token</li>
                    <li>If that fails, disconnect and reconnect the account</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="Sync stopped, and the schedule is back on No Sync">
                <ul class="doc-list">
                    <li>Microsoft ended the grant: access was removed in the Microsoft account, or a password change or sign-in policy invalidated it. When Microsoft reports that on the next token refresh, Event Schedule forgets the tokens and switches every schedule that user owns to No Sync, clearing its subscription and sync cursor</li>
                    <li>The calendar choice and the record of which event became which Outlook entry are kept, so reconnecting does not copy everything again</li>
                    <li>Connect again under <strong>Settings</strong> &rarr; <strong>Integrations</strong> &rarr; <strong>Outlook Calendar</strong>, then set the sync direction on each schedule and save</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="Events are not appearing in Outlook">
                <ul class="doc-list">
                    <li>The direction must be To Outlook Calendar or Bidirectional Sync, and a calendar must be selected</li>
                    <li>Only the schedule owner's connected account is used, so check who owns the schedule</li>
                    <li>Draft and Internal events are not pushed. Publish the event</li>
                    <li>Events created before sync was enabled are not pushed in bulk. Open each one and use <strong>Sync Now</strong> on its <strong>Calendar sync</strong> tab</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="The Calendar sync tab is missing on the event form">
                <ul class="doc-list">
                    <li>It only appears on a saved event, when the schedule has an Outlook calendar selected and its direction includes To Outlook Calendar</li>
                    <li>Select a calendar under <strong>Edit Schedule</strong> &rarr; <strong>Integrations</strong> &rarr; <strong>Outlook Calendar</strong> and save the schedule</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="Teams meeting link not created">
                <ul class="doc-list">
                    <li>Personal Microsoft accounts may not support Teams for Business meetings</li>
                    <li>In that case the app falls back to creating a normal event without a Teams link</li>
                    <li>The event must have no venue, and its <strong>Event URL</strong> must be empty for the join link to be saved</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="Inbound changes not updating">
                <ul class="doc-list">
                    <li>The direction must be From Outlook Calendar or Bidirectional Sync, otherwise no subscription exists</li>
                    <li>Confirm the app has a public HTTPS URL so Graph can reach the webhook endpoint</li>
                    <li>Inbound sync is queued, so if the cron entry stops, taking the queue worker with it, a working webhook looks exactly like a broken one</li>
                    <li>Without a public URL, rely on the 15-minute <code class="doc-inline-code">microsoft:sync</code> poll and confirm the scheduler cron is running</li>
                    <li>Outlook changes outside the window of 30 days back to 365 days ahead are not imported</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="An event deleted in Outlook is still here">
                <ul class="doc-list">
                    <li>This is the default. <strong>When an event is deleted in the connected calendar</strong> starts on <strong>Keep it here</strong></li>
                    <li>The control only appears once the direction is From Outlook Calendar or Bidirectional Sync. If your Google Calendar is connected too, it sits in the <strong>Google Calendar</strong> row instead, and only while that row's direction is From Google Calendar or Bidirectional Sync</li>
                    <li>Events with ticket sales or ad boost spend are marked cancelled rather than deleted, even when the policy is Delete it here</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="Subscription creation fails">
                <ul class="doc-list">
                    <li>Ensure <code class="doc-inline-code">MICROSOFT_WEBHOOK_SECRET</code> is set, as subscription creation is refused without it</li>
                    <li>Ensure the notification URL is publicly reachable over HTTPS, because Graph validates it before the subscription is created</li>
                    <li>The schedule still saves when the subscription cannot be created. The failure is only in the log, and inbound changes fall back to the 15-minute poll</li>
                </ul>
            </x-doc-faq>

            <x-doc-faq question="After switching calendars">
                <ul class="doc-list">
                    <li>The stored delta token belongs to one calendar, so changing the calendar clears it and the next run reads the whole window again</li>
                    <li>An expired or rejected delta token does the same thing once, automatically</li>
                    <li>Events already mapped, or matching an existing event by name and start time, are updated rather than duplicated</li>
                    <li>Copies pushed before the switch stay on the calendar they were created on and keep being updated there. Only events pushed for the first time land on the new calendar</li>
                </ul>
            </x-doc-faq>
        </div>

        <h3 id="logs" class="doc-subheading">Logs</h3>
        <p>Sync operations are logged in the application logs. Check <code class="doc-inline-code">storage/logs/laravel.log</code> for detailed information about sync operations, and <code class="doc-inline-code">storage/logs/scheduler.log</code> for the scheduled sync and subscription-renewal runs.</p>
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
                        <td>Microsoft access and refresh tokens are encrypted at rest per user, and are never included in API responses</td>
                    </tr>
                    <tr>
                        <td>Rotating refresh tokens</td>
                        <td>Microsoft rotates the refresh token on each refresh, and the app stores the latest one under a per-user lock so two workers cannot rotate at once</td>
                    </tr>
                    <tr>
                        <td>OAuth state check</td>
                        <td>The sign-in flow carries a random state value that must match the one held in the session, so a callback that was not started by the user is rejected</td>
                    </tr>
                    <tr>
                        <td>Webhook authentication</td>
                        <td><code class="doc-inline-code">MICROSOFT_WEBHOOK_SECRET</code> (the <code class="doc-inline-code">clientState</code>) authenticates inbound Graph notifications, and mismatched notifications are rejected. The endpoint is public by necessity and is rate limited</td>
                    </tr>
                    <tr>
                        <td>Delegated access only</td>
                        <td>The app requests delegated <code class="doc-inline-code">Calendars.ReadWrite</code>, so it can only reach the calendars of the user who signed in, not the whole tenant</td>
                    </tr>
                    <tr>
                        <td>Clean disconnect</td>
                        <td>Disconnecting deletes the Graph subscriptions of the schedules the user owns, switches them to No Sync, and deletes the stored tokens, event mappings and calendar selections. Nothing already in the Outlook calendar is deleted</td>
                    </tr>
                    <tr>
                        <td>Audit trail</td>
                        <td>Connecting, disconnecting and syncing a whole schedule are written to the audit log</td>
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
