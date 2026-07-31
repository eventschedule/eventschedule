<x-docs-page
    key="selfhost/microsoft-calendar"
    description="Set up bidirectional Outlook Calendar sync with Event Schedule using Microsoft Graph. Automatically sync events between both platforms."
    lede="Set up and use the Microsoft 365 / Outlook Calendar integration for bidirectional sync between Event Schedule and Outlook through Microsoft Graph."
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
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
            Prerequisites
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Outlook Calendar sync is a free-tier feature, so no plan gate applies. Once these server credentials are in place, every schedule on the install can use it.</p>
        <ol class="doc-list doc-list-numbered">
            <li>A Microsoft Entra ID (Azure AD) tenant or an Azure account to register an application</li>
            <li>Access to the Azure Portal to create an app registration</li>
            <li>A redirect URI that matches your app registration exactly, so users can complete the OAuth sign-in</li>
            <li>For near-real-time inbound sync, a publicly reachable HTTPS URL, because Microsoft Graph must be able to call the webhook endpoint on your server</li>
        </ol>

        <div class="doc-callout doc-callout-info mt-6">
            <div class="doc-callout-title">No public URL?</div>
            <p>Installs without a public HTTPS URL still work. Instead of near-real-time webhooks, inbound changes are picked up by the 15-minute <code class="doc-inline-code">microsoft:sync</code> polling fallback.</p>
        </div>

        <div class="doc-callout doc-callout-warning mt-6">
            <div class="doc-callout-title">Queue worker required for webhooks</div>
            <p>For near-real-time webhooks, run an asynchronous queue (set <code class="doc-inline-code">QUEUE_CONNECTION=database</code> and keep <code class="doc-inline-code">php artisan queue:work</code> running). Inbound sync is dispatched to the queue so Microsoft Graph gets a fast response. On the default <code class="doc-inline-code">sync</code> connection the sync runs inside the webhook request, which can be slow enough that Graph deprovisions the subscription. Without a worker, inbound changes still arrive via the 15-minute poll.</p>
        </div>
    </section>

    <!-- Setup Instructions -->
    <section id="setup" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Setup Instructions
        </h2>

        <h3 class="doc-subheading">1. Azure App Registration</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to the <a href="https://portal.azure.com/" target="_blank" rel="noopener noreferrer" class="doc-link">Azure Portal</a> and open <strong class="text-gray-900 dark:text-white">Microsoft Entra ID</strong> &rarr; <strong class="text-gray-900 dark:text-white">App registrations</strong> &rarr; <strong class="text-gray-900 dark:text-white">New registration</strong></li>
            <li>Enter a name for the application (for example, "Event Schedule")</li>
            <li>Under <strong class="text-gray-900 dark:text-white">Supported account types</strong>, choose "Accounts in any organizational directory and personal Microsoft accounts" (this matches <code class="doc-inline-code">MICROSOFT_TENANT=common</code>)</li>
            <li>Under <strong class="text-gray-900 dark:text-white">Redirect URI</strong>, select the <strong class="text-gray-900 dark:text-white">Web</strong> platform and enter: <code class="doc-inline-code">{APP_URL}/microsoft-calendar/callback</code></li>
            <li>Click <strong class="text-gray-900 dark:text-white">Register</strong></li>
        </ol>

        <h3 class="doc-subheading">2. API Permissions</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Event Schedule requests delegated permissions only, so it acts as the signed-in user and never gains tenant-wide calendar access.</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>In the app registration, open <strong class="text-gray-900 dark:text-white">API permissions</strong> &rarr; <strong class="text-gray-900 dark:text-white">Add a permission</strong> &rarr; <strong class="text-gray-900 dark:text-white">Microsoft Graph</strong> &rarr; <strong class="text-gray-900 dark:text-white">Delegated permissions</strong></li>
            <li>Add the following delegated permissions:
                <ul class="doc-list mt-2 mb-2">
                    <li><code class="doc-inline-code">Calendars.ReadWrite</code></li>
                    <li><code class="doc-inline-code">offline_access</code></li>
                    <li><code class="doc-inline-code">openid</code></li>
                    <li><code class="doc-inline-code">email</code></li>
                    <li><code class="doc-inline-code">profile</code></li>
                </ul>
            </li>
            <li>If your tenant requires it, grant admin consent for the permissions</li>
        </ol>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">The scope list is fixed</div>
            <p>Event Schedule always requests exactly these five scopes. <code class="doc-inline-code">offline_access</code> is the one that yields a refresh token, so without it users are pushed back through sign-in as soon as the access token expires.</p>
        </div>

        <h3 class="doc-subheading">3. Client Secret and Client ID</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Certificates &amp; secrets</strong> &rarr; <strong class="text-gray-900 dark:text-white">New client secret</strong>, then copy the secret <strong class="text-gray-900 dark:text-white">Value</strong> immediately (it is only shown once)</li>
            <li>Open the <strong class="text-gray-900 dark:text-white">Overview</strong> page and copy the <strong class="text-gray-900 dark:text-white">Application (client) ID</strong></li>
        </ol>

        <h3 class="doc-subheading">4. Environment Configuration</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Add the following environment variables to your <code class="doc-inline-code">.env</code> file:</p>

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

        <p class="text-gray-600 dark:text-gray-300 mb-6">If you cache your configuration, run <code class="doc-inline-code">php artisan config:cache</code> after editing <code class="doc-inline-code">.env</code>, or the new values will not be picked up.</p>

        <h3 class="doc-subheading">Variable Reference</h3>
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

        <div class="doc-callout doc-callout-warning mt-6">
            <div class="doc-callout-title">Webhook secret is the only authenticity check</div>
            <p>Graph subscriptions cannot be created at all while <code class="doc-inline-code">MICROSOFT_WEBHOOK_SECRET</code> is empty: the attempt is refused rather than made without a <code class="doc-inline-code">clientState</code>. Once set, Graph echoes the value back on every change notification, and Event Schedule ignores any notification whose value does not match, answering <code class="doc-inline-code">401</code> when none of them do.</p>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            Features
        </h2>

        <h3 class="doc-subheading">How Sync Works</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong class="text-gray-900 dark:text-white">Connect an account:</strong> A user connects their Microsoft account through OAuth from <strong class="text-gray-900 dark:text-white">Settings</strong> &rarr; <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong>. The tokens are stored on that user record</li>
            <li><strong class="text-gray-900 dark:text-white">Per-schedule calendar and direction:</strong> The schedule owner picks one Outlook calendar and a sync direction on <strong class="text-gray-900 dark:text-white">Integrations</strong> &rarr; <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong> of the schedule edit page</li>
            <li><strong class="text-gray-900 dark:text-white">Outbound:</strong> Publishing, editing or deleting an event pushes the change to the selected calendar</li>
            <li><strong class="text-gray-900 dark:text-white">Inbound:</strong> Microsoft Graph subscriptions notify the webhook endpoint, and a queued job pulls the changes in with a Graph delta query</li>
            <li><strong class="text-gray-900 dark:text-white">Polling fallback:</strong> A 15-minute <code class="doc-inline-code">microsoft:sync</code> command catches anything webhooks miss, and is the only inbound path on installs without a public URL</li>
            <li><strong class="text-gray-900 dark:text-white">Subscription renewal:</strong> A daily <code class="doc-inline-code">microsoft:refresh-webhooks</code> command renews Graph subscriptions, which are created with a 60-hour (about 2.5 day) expiry</li>
        </ol>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Outlook sync uses the schedule owner's account only</div>
            <p>The calendar selection, the Graph subscription, and every outbound and inbound sync run on the schedule owner's connected Microsoft account. Team members do not get their own Outlook sync, and the Outlook Calendar tab only shows the settings to the owner. Per-member calendar sync is a Google Calendar feature.</p>
        </div>

        <h3 class="doc-subheading">Sync Direction</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Each schedule picks one of four options. Saving the tab creates or removes the Graph subscription for you.</p>
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
                        <td><span class="font-semibold text-gray-900 dark:text-white">To Outlook Calendar</span></td>
                        <td>Published Event Schedule events are pushed to the selected Outlook calendar</td>
                        <td>Not created</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">From Outlook Calendar</span></td>
                        <td>Outlook events are imported into Event Schedule</td>
                        <td>Created</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Bidirectional Sync</span></td>
                        <td>Both of the above: changes flow in each direction</td>
                        <td>Created</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">No Sync</span></td>
                        <td>Outlook sync is turned off for this schedule. Events already in Outlook are left alone</td>
                        <td>Removed</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Event Information Synced</h3>
        <ul class="doc-list mb-6">
            <li>Event name, as the Outlook subject</li>
            <li>Description. By default the event description is sent as plain text; if the schedule has a <strong class="text-gray-900 dark:text-white">Calendar Description Template</strong> set on <strong class="text-gray-900 dark:text-white">Integrations</strong> &rarr; <strong class="text-gray-900 dark:text-white">Advanced</strong>, the rendered template is sent instead (see the <a href="{{ route('marketing.docs.creating_schedules') }}#available-variables" class="doc-link">available variables</a>). On an update with no template and an empty description, no body is sent at all, so notes you typed on the Outlook copy survive</li>
            <li>Start time in the schedule's timezone, and an end time calculated from the event duration</li>
            <li>Location, taken from the venue's best available address</li>
            <li>Privacy: an unlisted event is marked <strong class="text-gray-900 dark:text-white">Private</strong> in Outlook, everything else is normal</li>
            <li>A Microsoft Teams join link, for online events when the toggle is enabled</li>
        </ul>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">What is not sent</div>
            <p>Draft events are never pushed, so an event first appears in Outlook when you publish it. Events with no duration are sent as all-day Outlook events. Recurring events are sent as a single Outlook entry on the series start date rather than as an Outlook recurrence. Use the schedule's iCal feed when you need every date of a series in a calendar app.</p>
        </div>

        <h3 class="doc-subheading">Microsoft Teams Meeting Links</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Enable <strong class="text-gray-900 dark:text-white">Create Teams meetings for online events</strong> on the schedule's Outlook Calendar tab. Every event with no venue is then created in Outlook as a Teams for Business meeting, and the join link is written into the event's online event URL, but only when that field is still empty so a link you entered yourself is never overwritten.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Personal Microsoft accounts usually cannot create Teams for Business meetings. When Graph rejects the request, Event Schedule retries immediately without the Teams flags, so you get a normal Outlook event and no join link rather than a failed sync.</p>

        <h3 class="doc-subheading">Importing From Outlook</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Inbound sync uses a Graph delta query over a window running from 30 days ago to 365 days ahead. On the first run, or after you switch calendars, the whole window is read; after that only changes are fetched. Imported events:</p>
        <ul class="doc-list mb-6">
            <li>Arrive already approved and use the schedule's slug pattern and default category</li>
            <li>Convert the Outlook location into a venue, reusing one of your existing venues when the name or address matches</li>
            <li>Are matched to an existing event by name and start time when there is no stored mapping yet, so an event you pushed out does not come back as a duplicate</li>
        </ul>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Appointment bookings are protected</div>
            <p>An event created by an appointment booking is owned by Event Schedule. Inbound sync never rewrites its name, description or time, so moving the Outlook copy will not move a customer's booking.</p>
        </div>

        <h3 class="doc-subheading">When an Event Is Deleted in Outlook</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Schedules that import from Outlook also choose <strong class="text-gray-900 dark:text-white">When an event is deleted in the connected calendar</strong>. The setting is shared with the Google Calendar integration, so if you have also connected Google Calendar the control appears on the Google tab instead of the Outlook tab and one choice covers both.</p>
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
                        <td><span class="font-semibold text-gray-900 dark:text-white">Keep it here</span></td>
                        <td>The event stays exactly as it is. This is the default</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Mark as cancelled</span></td>
                        <td>The event is hidden but recoverable. Recommended when tickets are sold</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Delete it here</span></td>
                        <td>The event is removed. Events with ticket sales or an active ad boost are hidden instead of deleted</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mt-4">Only a real deletion in Outlook triggers this policy. An event that merely moves outside the sync window is left untouched.</p>
        <p class="text-gray-600 dark:text-gray-300 mt-4 mb-6">An event that belongs to more than one schedule is only detached from this schedule, never cancelled or deleted outright, unless this schedule is the one that owns it. The other schedules keep the event as it is.</p>

        <h3 class="doc-subheading">Real-Time Sync and Polling Fallback</h3>
        <ul class="doc-list">
            <li>Graph sends one notification per changed event; Event Schedule collapses them to at most one inbound sync per schedule per minute, then reads every pending change in a single delta request</li>
            <li>The 15-minute <code class="doc-inline-code">microsoft:sync</code> command polls the schedules whose direction is <strong class="text-gray-900 dark:text-white">From Outlook Calendar</strong> or <strong class="text-gray-900 dark:text-white">Bidirectional Sync</strong>, and is the primary path when no public URL is available</li>
            <li>The daily <code class="doc-inline-code">microsoft:refresh-webhooks</code> command extends any subscription due to expire within the next day, and recreates any that Graph has already dropped</li>
        </ul>
    </section>

    <!-- Usage -->
    <section id="usage" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
            </svg>
            Usage
        </h2>

        <h3 class="doc-subheading">Step by Step</h3>

        <div class="space-y-6 mb-8">
            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">1. Connect Outlook Calendar</h4>
                <ol class="doc-list doc-list-numbered text-sm">
                    <li>Go to your settings page (<code class="doc-inline-code">/settings</code>)</li>
                    <li>Find the "Outlook Calendar" section</li>
                    <li>Click "Connect Outlook Calendar"</li>
                    <li>Authorize the application in the Microsoft sign-in flow</li>
                </ol>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-3">If the section shows "Outlook Calendar is not configured on this server" instead of the button, <code class="doc-inline-code">MICROSOFT_CLIENT_ID</code> is still missing from <code class="doc-inline-code">.env</code>.</p>
            </div>

            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">2. Choose a Calendar and Sync Direction</h4>
                <ol class="doc-list doc-list-numbered text-sm">
                    <li>Open <strong class="text-gray-900 dark:text-white">Admin Panel</strong> &rarr; <strong class="text-gray-900 dark:text-white">Schedule</strong> &rarr; <strong class="text-gray-900 dark:text-white">Edit</strong></li>
                    <li>Go to the <strong class="text-gray-900 dark:text-white">Integrations</strong> section and select the <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong> tab</li>
                    <li>Pick which Outlook calendar to sync with</li>
                    <li>Choose the sync direction: To Outlook Calendar, From Outlook Calendar, Bidirectional Sync, or No Sync</li>
                    <li>Save the schedule. Only the schedule owner sees these controls</li>
                </ol>
            </div>

            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">3. Push Events to Outlook</h4>
                <ol class="doc-list doc-list-numbered text-sm">
                    <li>Create and publish events as usual</li>
                    <li>Every event published or edited from then on is pushed to the selected calendar automatically</li>
                    <li>Events that already existed when you turned sync on are not pushed in bulk, so add them with the per-event button below</li>
                </ol>
            </div>

            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">4. Sync a Single Event by Hand</h4>
                <ol class="doc-list doc-list-numbered text-sm">
                    <li>Open the event edit page</li>
                    <li>Go to the <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong> section</li>
                    <li>Click "Sync to Outlook Calendar", or "Remove from Outlook Calendar" to delete the Outlook copy</li>
                    <li>The section only appears once a calendar is selected and the direction includes To Outlook Calendar</li>
                </ol>
            </div>

            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">5. Enable Teams Meeting Links</h4>
                <ol class="doc-list doc-list-numbered text-sm">
                    <li>On the schedule's Outlook Calendar tab, turn on <strong class="text-gray-900 dark:text-white">Create Teams meetings for online events</strong></li>
                    <li>Events with no venue are created as Teams meetings, and the join link is saved to the event when it has no link yet</li>
                </ol>
            </div>
        </div>

        <h3 class="doc-subheading">Automatic Sync</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once the schedule owner has connected Outlook and the direction includes <strong class="text-gray-900 dark:text-white">To Outlook Calendar</strong>, events are synced automatically when they are:</p>
        <ul class="doc-list mb-6">
            <li>Published, whether that is a new event or a draft you just published</li>
            <li>Edited, which updates the existing Outlook entry in place</li>
            <li>Deleted, cancelled or un-published, which removes the Outlook copy</li>
        </ul>

        <div class="doc-callout doc-callout-info mt-6">
            <div class="doc-callout-title">Scheduled Sync</div>
            <p>Inbound polling and subscription renewal run through scheduled commands (<code class="doc-inline-code">microsoft:sync</code> every 15 minutes and <code class="doc-inline-code">microsoft:refresh-webhooks</code> daily). Make sure the Laravel scheduler cron is active: <code class="doc-inline-code">* * * * * php artisan schedule:run</code></p>
        </div>
    </section>

    <!-- API Endpoints -->
    <section id="api-endpoints" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
            </svg>
            API Endpoints
        </h2>

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
                        <td>Schedule member</td>
                        <td>Sync a whole schedule. Send <code class="doc-inline-code">sync_direction</code> as <code class="doc-inline-code">to</code>, <code class="doc-inline-code">from</code> or <code class="doc-inline-code">both</code>, or omit it to use the schedule's saved direction. There is no button for this, so it is the way to backfill events that predate the connection</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /microsoft-calendar/sync-event/{subdomain}/{eventId}</code></td>
                        <td>Schedule member</td>
                        <td>Sync a specific event. This is what the per-event <strong class="text-gray-900 dark:text-white">Sync to Outlook Calendar</strong> button calls</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">DELETE /microsoft-calendar/unsync-event/{subdomain}/{eventId}</code></td>
                        <td>Schedule member</td>
                        <td>Remove the event's Outlook copy and drop its mapping</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GET /microsoft-calendar/webhook</code></td>
                        <td>Public, throttled</td>
                        <td>Microsoft Graph subscription validation handshake</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /microsoft-calendar/webhook</code></td>
                        <td>Public, throttled, <code class="doc-inline-code">clientState</code></td>
                        <td>Microsoft Graph change notifications (also handles the validation handshake)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Scheduled Commands</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">These Artisan commands keep inbound sync and Graph subscriptions healthy:</p>
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

        <p class="text-gray-600 dark:text-gray-300 mb-4">These commands run through the Laravel scheduler, which requires the following cron entry:</p>
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
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Troubleshooting
        </h2>

        <h3 class="doc-subheading">Common Issues</h3>

        <div class="doc-fields mb-8">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">No refresh token / repeated re-authentication</h4>
                <ul class="doc-list text-sm">
                    <li>Make sure the <code class="doc-inline-code">offline_access</code> scope is granted in the app registration</li>
                    <li>Visit <code class="doc-inline-code">/microsoft-calendar/reauthorize</code> to force a fresh consent prompt, which is what returns a new refresh token</li>
                    <li>If that fails, disconnect and reconnect the account</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Events are not appearing in Outlook</h4>
                <ul class="doc-list text-sm">
                    <li>The direction must be To Outlook Calendar or Bidirectional Sync, and a calendar must be selected</li>
                    <li>Only the schedule owner's connected account is used, so check who owns the schedule</li>
                    <li>Draft events are not pushed. Publish the event, or use the per-event Sync to Outlook Calendar button</li>
                    <li>Events created before sync was enabled are not pushed in bulk</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Teams meeting link not created</h4>
                <ul class="doc-list text-sm">
                    <li>Personal Microsoft accounts may not support Teams for Business meetings</li>
                    <li>In that case the app falls back to creating a normal event without a Teams link</li>
                    <li>The event must have no venue, and its online event URL must be empty for the join link to be saved</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Inbound changes not updating</h4>
                <ul class="doc-list text-sm">
                    <li>The direction must be From Outlook Calendar or Bidirectional Sync, otherwise no subscription exists</li>
                    <li>Confirm the app has a public HTTPS URL so Graph can reach the webhook endpoint</li>
                    <li>Inbound sync is queued, so a stopped queue worker looks exactly like a broken webhook</li>
                    <li>Without a public URL, rely on the 15-minute <code class="doc-inline-code">microsoft:sync</code> poll and confirm the scheduler cron is running</li>
                    <li>Outlook changes outside the window of 30 days back to 365 days ahead are not imported</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">An event deleted in Outlook is still here</h4>
                <ul class="doc-list text-sm">
                    <li>This is the default. <strong class="text-gray-900 dark:text-white">When an event is deleted in the connected calendar</strong> starts on <strong class="text-gray-900 dark:text-white">Keep it here</strong></li>
                    <li>The control only appears once the direction is From Outlook Calendar or Bidirectional Sync, and it sits on the Google Calendar tab if that account is also connected</li>
                    <li>Events with ticket sales or an active ad boost are hidden rather than deleted, even when the policy is Delete it here</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Subscription creation fails</h4>
                <ul class="doc-list text-sm">
                    <li>Ensure <code class="doc-inline-code">MICROSOFT_WEBHOOK_SECRET</code> is set, as subscription creation is refused without it</li>
                    <li>Ensure the notification URL is publicly reachable over HTTPS, because Graph validates it before the subscription is created</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">A full re-scan after switching calendars</h4>
                <ul class="doc-list text-sm">
                    <li>The stored delta token belongs to one calendar, so changing the calendar clears it and the next run reads the whole window again</li>
                    <li>An expired or rejected delta token does the same thing once, automatically</li>
                    <li>Events already mapped, or matching an existing event by name and start time, are updated rather than duplicated</li>
                </ul>
            </div>
        </div>

        <h3 class="doc-subheading">Logs</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Sync operations are logged in the application logs. Check <code class="doc-inline-code">storage/logs/laravel.log</code> for detailed information about sync operations.</p>
    </section>

    <!-- Security Considerations -->
    <section id="security" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            Security Considerations
        </h2>
        <ol class="doc-list doc-list-numbered">
            <li><span class="font-semibold text-gray-900 dark:text-white">Token Storage:</span> Microsoft access and refresh tokens are encrypted at rest per user, and are never included in API responses</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Rotating Refresh Tokens:</span> Microsoft rotates the refresh token on each refresh, and the app stores the latest one under a per-user lock so two workers cannot rotate at once</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">OAuth State Check:</span> The sign-in flow carries a random state value that must match the one held in the session, so a callback that was not started by the user is rejected</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Webhook Authentication:</span> <code class="doc-inline-code">MICROSOFT_WEBHOOK_SECRET</code> (the <code class="doc-inline-code">clientState</code>) authenticates inbound Graph notifications, and mismatched notifications are rejected. The endpoint is public by necessity and is rate limited</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Delegated Access Only:</span> The app requests delegated <code class="doc-inline-code">Calendars.ReadWrite</code>, so it can only reach the calendars of the user who signed in, not the whole tenant</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Secrets in .env:</span> Keep the client secret and webhook secret in <code class="doc-inline-code">.env</code>, and never commit them to source control</li>
        </ol>
    </section>
</x-docs-page>
