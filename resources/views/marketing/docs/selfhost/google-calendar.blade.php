<x-marketing-layout>
    <x-slot name="title">Google Calendar Integration Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Google Calendar</x-slot>
    <x-slot name="description">Set up bidirectional Google Calendar sync with Event Schedule. Automatically sync events between both platforms.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Google Calendar Integration Documentation - Event Schedule",
        "description": "Set up bidirectional Google Calendar sync with Event Schedule. Automatically sync events between both platforms.",
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
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Google Calendar" section="selfhost" sectionTitle="Selfhost" sectionRoute="marketing.docs.selfhost" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Google Calendar Integration</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Set up and use the Google Calendar integration feature for bidirectional sync between Event Schedule and Google Calendar.
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
                        <a href="#prerequisites" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Prerequisites</a>
                        <a href="#setup" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Setup Instructions</a>
                        <a href="#features" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Features</a>
                        <a href="#usage" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Usage</a>
                        <a href="#api-endpoints" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">API Endpoints</a>
                        <a href="#troubleshooting" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Troubleshooting</a>
                        <a href="#security" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Security Considerations</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Prerequisites -->
                        <section id="prerequisites" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                </svg>
                                Prerequisites
                            </h2>
                            <ol class="doc-list doc-list-numbered">
                                <li>A Google Cloud Console project</li>
                                <li>Google Calendar API enabled</li>
                                <li>OAuth 2.0 credentials configured</li>
                            </ol>
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

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">1. Google Cloud Console Setup</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to the <a href="https://console.cloud.google.com/" target="_blank" rel="noopener noreferrer" class="text-emerald-400 hover:text-emerald-300">Google Cloud Console</a></li>
                                <li>Create a new project or select an existing one</li>
                                <li>Enable the Google Calendar API:
                                    <ul class="doc-list mt-2 mb-2">
                                        <li>Go to "APIs & Services" > "Library"</li>
                                        <li>Search for "Google Calendar API"</li>
                                        <li>Click on it and press "Enable"</li>
                                    </ul>
                                </li>
                            </ol>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">2. OAuth 2.0 Credentials</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to "APIs & Services" > "Credentials"</li>
                                <li>Click "Create Credentials" > "OAuth 2.0 Client IDs"</li>
                                <li>Choose "Web application" as the application type</li>
                                <li>Add authorized redirect URIs:
                                    <ul class="doc-list mt-2 mb-2">
                                        <li>For development: <code class="doc-inline-code">http://localhost:8000/google-calendar/callback</code></li>
                                        <li>For production: <code class="doc-inline-code">https://yourdomain.com/google-calendar/callback</code></li>
                                    </ul>
                                </li>
                                <li>Save the credentials and note down the Client ID and Client Secret</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">3. Environment Configuration</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Add the following environment variables to your <code class="doc-inline-code">.env</code> file:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-variable">GOOGLE_CLIENT_ID</span>=<span class="code-string">your_google_client_id</span>
<span class="code-variable">GOOGLE_CLIENT_SECRET</span>=<span class="code-string">your_google_client_secret</span>
<span class="code-variable">GOOGLE_REDIRECT_URI</span>=<span class="code-string">https://yourdomain.com/google-calendar/callback</span></code></pre>
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

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User Features</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Connect Google Calendar:</strong> Users can connect their Google Calendar account from their profile page</li>
                                <li><strong class="text-gray-900 dark:text-white">Sync All Events:</strong> Users can sync all their events to Google Calendar at once</li>
                                <li><strong class="text-gray-900 dark:text-white">Individual Event Sync:</strong> Users can sync individual events from the event edit page</li>
                                <li><strong class="text-gray-900 dark:text-white">Automatic Sync:</strong> Events are automatically synced when created, updated, or deleted (if user has Google Calendar connected)</li>
                                <li><strong class="text-gray-900 dark:text-white">Bidirectional Sync:</strong> Events added in either Google Calendar or Event Schedule will appear in both places</li>
                                <li><strong class="text-gray-900 dark:text-white">Calendar Selection:</strong> Choose which Google Calendar to sync with for each role/schedule</li>
                                <li><strong class="text-gray-900 dark:text-white">Real-time Updates:</strong> Changes in Google Calendar are automatically synced to Event Schedule via webhooks</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Event Information Synced</h3>
                            <ul class="doc-list mb-6">
                                <li>Event name</li>
                                <li>Event description (with venue and URL information)</li>
                                <li>Start and end times</li>
                                <li>Location (venue address)</li>
                                <li>Event URL</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sync Status</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Events can have three sync statuses:</p>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Not Connected</span></td>
                                            <td>User hasn't connected their Google Calendar</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Not Synced</span></td>
                                            <td>User has connected Google Calendar but this event isn't synced</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Synced</span></td>
                                            <td>Event is synced to Google Calendar</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Bidirectional Sync</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">When bidirectional sync is enabled:</p>
                            <ul class="doc-list">
                                <li>Events created in Event Schedule are automatically added to Google Calendar</li>
                                <li>Events created in Google Calendar are automatically added to Event Schedule</li>
                                <li>Changes in either system are reflected in the other</li>
                                <li>Real-time updates via Google Calendar webhooks</li>
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

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">For Users</h3>

                            <div class="space-y-6 mb-8">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">1. Connect Google Calendar</h4>
                                    <ol class="doc-list doc-list-numbered text-sm">
                                        <li>Go to your settings page (<code class="doc-inline-code">/settings</code>)</li>
                                        <li>Scroll to the "Google Calendar Integration" section</li>
                                        <li>Click "Connect Google Calendar"</li>
                                        <li>Authorize the application in the Google OAuth flow</li>
                                    </ol>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">2. Sync All Events</h4>
                                    <ol class="doc-list doc-list-numbered text-sm">
                                        <li>After connecting, click "Sync All Events" in your profile</li>
                                        <li>This will sync all your events to your primary Google Calendar</li>
                                    </ol>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">3. Sync Individual Events</h4>
                                    <ol class="doc-list doc-list-numbered text-sm">
                                        <li>Go to any event edit page</li>
                                        <li>Scroll to the "Google Calendar Sync" section</li>
                                        <li>Click "Sync to Google Calendar" or "Remove from Google Calendar"</li>
                                    </ol>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">4. Enable Bidirectional Sync</h4>
                                    <ol class="doc-list doc-list-numbered text-sm">
                                        <li>Go to your role/schedule edit page</li>
                                        <li>Scroll to the "Google Calendar Integration" section</li>
                                        <li>Select which Google Calendar to sync with</li>
                                        <li>Click "Enable" for bidirectional sync</li>
                                        <li>Events will now sync both ways automatically</li>
                                    </ol>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">5. Manual Sync Options</h4>
                                    <ul class="doc-list text-sm">
                                        <li><strong class="text-gray-900 dark:text-white">Sync to Google Calendar:</strong> Push all events from Event Schedule to Google Calendar</li>
                                        <li><strong class="text-gray-900 dark:text-white">Sync from Google Calendar:</strong> Pull all events from Google Calendar to Event Schedule</li>
                                    </ul>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">For Developers</h3>

                            <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Automatic Sync</h4>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Events are automatically synced when:</p>
                            <ul class="doc-list mb-6">
                                <li>Created (if user has Google Calendar connected)</li>
                                <li>Updated (if user has Google Calendar connected)</li>
                                <li>Deleted (if event was previously synced)</li>
                            </ul>

                            <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Manual Sync</h4>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">You can manually sync events using the Event model:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>PHP</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-comment">// Sync an event for all connected users</span>
<span class="code-variable">$event</span>-><span class="code-keyword">syncToGoogleCalendar</span>(<span class="code-string">'create'</span>); <span class="code-comment">// or 'update', 'delete'</span>

<span class="code-comment">// Check sync status</span>
<span class="code-variable">$event</span>-><span class="code-keyword">isSyncedToGoogleCalendar</span>();

<span class="code-comment">// Get sync status for a specific user</span>
<span class="code-variable">$event</span>-><span class="code-keyword">getGoogleCalendarSyncStatus</span>(<span class="code-variable">$user</span>);</code></pre>
                            </div>

                            <div class="doc-callout doc-callout-info mt-6">
                                <div class="doc-callout-title">Background Jobs</div>
                                <p>Sync operations are handled by background jobs (<code class="doc-inline-code">SyncEventToGoogleCalendar</code>) to avoid blocking the user interface.</p>
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

                            <div class="overflow-x-auto mb-6">
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
                                            <td>Start OAuth flow</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /google-calendar/callback</code></td>
                                            <td>OAuth callback</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /google-calendar/disconnect</code></td>
                                            <td>Disconnect Google Calendar</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /google-calendar/calendars</code></td>
                                            <td>Get user's calendars</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /google-calendar/sync-events</code></td>
                                            <td>Sync all events</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /google-calendar/sync-event/{eventId}</code></td>
                                            <td>Sync specific event</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">DELETE /google-calendar/unsync-event/{eventId}</code></td>
                                            <td>Remove event from Google Calendar</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /google-calendar/role/{subdomain}</code></td>
                                            <td>Update role's Google Calendar selection</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /google-calendar/bidirectional/{subdomain}/enable</code></td>
                                            <td>Enable bidirectional sync</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /google-calendar/bidirectional/{subdomain}/disable</code></td>
                                            <td>Disable bidirectional sync</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /google-calendar/sync-from-google/{subdomain}</code></td>
                                            <td>Sync from Google Calendar to Event Schedule</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /google-calendar/webhook</code></td>
                                            <td>Webhook verification (Google Calendar)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /google-calendar/webhook</code></td>
                                            <td>Webhook handler (Google Calendar)</td>
                                        </tr>
                                    </tbody>
                                </table>
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

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Common Issues</h3>

                            <div class="space-y-4 mb-8">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Google Calendar not connected" error</h4>
                                    <ul class="doc-list text-sm">
                                        <li>User needs to connect their Google Calendar account first</li>
                                        <li>Check if OAuth credentials are correctly configured</li>
                                    </ul>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Failed to sync event" error</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Check if Google Calendar API is enabled</li>
                                        <li>Verify OAuth credentials are correct</li>
                                        <li>Check application logs for detailed error messages</li>
                                    </ul>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Events not syncing automatically</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Ensure queue workers are running for background jobs</li>
                                        <li>Check if user has valid Google Calendar tokens</li>
                                    </ul>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Logs</h3>
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
                                <li><span class="font-semibold text-gray-900 dark:text-white">Token Storage:</span> Google OAuth tokens are stored encrypted in the database</li>
                                <li><span class="font-semibold text-gray-900 dark:text-white">Scope Limitation:</span> The integration only requests necessary Google Calendar permissions</li>
                                <li><span class="font-semibold text-gray-900 dark:text-white">User Authorization:</span> Users can only sync events they have access to</li>
                                <li><span class="font-semibold text-gray-900 dark:text-white">Token Refresh:</span> Access tokens are automatically refreshed when needed</li>
                            </ol>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
