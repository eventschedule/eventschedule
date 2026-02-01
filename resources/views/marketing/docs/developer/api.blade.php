<x-marketing-layout>
    <x-slot name="title">API Reference - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">API Reference</x-slot>
    <x-slot name="description">Programmatically manage schedules and events with the Event Schedule REST API. Learn about authentication, endpoints, and response formats.</x-slot>
    <x-slot name="keywords">event schedule api, rest api, api documentation, api reference, schedule api, events api</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="API Reference" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/20">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">API Reference</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Programmatically manage schedules and events with the Event Schedule REST API. API access requires a Pro plan.
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
                        <a href="#authentication" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Authentication</a>
                        <a href="#response-format" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Response Format</a>
                        <a href="#pagination" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Pagination</a>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 mt-6">Endpoints</div>
                        <a href="#list-schedules" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">List Schedules</a>
                        <a href="#list-events" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">List Events</a>
                        <a href="#create-event" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Create Event</a>
                        <a href="#create-sale" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Create Sale</a>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 mt-6">Reference</div>
                        <a href="#error-handling" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Error Handling</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Authentication -->
                        <section id="authentication" class="doc-section">
                            <h2 class="doc-heading">Authentication</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">All API requests must include your API key in the <code class="doc-inline-code">X-API-Key</code> header. You can generate an API key from your account settings.</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>Request Headers</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code>X-API-Key: your_api_key_here</code></pre>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL Example</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/schedules"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">API Key Security</div>
                                <p>Keep your API key secure and never expose it in client-side code. If you believe your key has been compromised, you can regenerate it from your account settings.</p>
                            </div>
                        </section>

                        <!-- Response Format -->
                        <section id="response-format" class="doc-section">
                            <h2 class="doc-heading">Response Format</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">All API responses follow a consistent format with two main properties:</p>

                            <ul class="doc-list mb-6">
                                <li><code class="doc-inline-code">data</code> - Contains the response payload (array for lists, object for single items)</li>
                                <li><code class="doc-inline-code">meta</code> - Contains metadata about the response, including pagination information</li>
                            </ul>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>Example Response</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code>{
    <span class="code-string">"data"</span>: [...],
    <span class="code-string">"meta"</span>: {
        <span class="code-string">"current_page"</span>: <span class="code-value">1</span>,
        <span class="code-string">"from"</span>: <span class="code-value">1</span>,
        <span class="code-string">"last_page"</span>: <span class="code-value">5</span>,
        <span class="code-string">"per_page"</span>: <span class="code-value">100</span>,
        <span class="code-string">"to"</span>: <span class="code-value">100</span>,
        <span class="code-string">"total"</span>: <span class="code-value">450</span>,
        <span class="code-string">"path"</span>: <span class="code-string">"/api/schedules"</span>
    }
}</code></pre>
                            </div>
                        </section>

                        <!-- Pagination -->
                        <section id="pagination" class="doc-section">
                            <h2 class="doc-heading">Pagination</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">List endpoints support pagination through query parameters:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Parameter</th>
                                            <th>Default</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">page</code></td>
                                            <td>1</td>
                                            <td>Page number to retrieve</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">per_page</code></td>
                                            <td>100</td>
                                            <td>Number of items per page (max: 1000)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>Pagination Example</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/schedules?page=2&per_page=50"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                            </div>
                        </section>

                        <!-- List Schedules -->
                        <section id="list-schedules" class="doc-section">
                            <h2 class="doc-heading">List Schedules</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                                <code class="doc-inline-code">/api/schedules</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of all schedules you have access to.</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/schedules?page=1&per_page=100"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>Response</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code>{
    <span class="code-string">"data"</span>: [
        {
            <span class="code-string">"id"</span>: <span class="code-string">"123"</span>,
            <span class="code-string">"url"</span>: <span class="code-string">"{{ config('app.url') }}/venue-name"</span>,
            <span class="code-string">"type"</span>: <span class="code-string">"venue"</span>,
            <span class="code-string">"name"</span>: <span class="code-string">"My Venue"</span>,
            <span class="code-string">"email"</span>: <span class="code-string">"venue@example.com"</span>,
            <span class="code-string">"website"</span>: <span class="code-string">"https://example.com"</span>,
            <span class="code-string">"description"</span>: <span class="code-string">"Venue description"</span>,
            <span class="code-string">"address1"</span>: <span class="code-string">"123 Main St"</span>,
            <span class="code-string">"city"</span>: <span class="code-string">"New York"</span>,
            <span class="code-string">"state"</span>: <span class="code-string">"NY"</span>,
            <span class="code-string">"postal_code"</span>: <span class="code-string">"10001"</span>,
            <span class="code-string">"country_code"</span>: <span class="code-string">"US"</span>
        }
    ],
    <span class="code-string">"meta"</span>: {
        <span class="code-string">"current_page"</span>: <span class="code-value">1</span>,
        <span class="code-string">"from"</span>: <span class="code-value">1</span>,
        <span class="code-string">"last_page"</span>: <span class="code-value">1</span>,
        <span class="code-string">"per_page"</span>: <span class="code-value">100</span>,
        <span class="code-string">"to"</span>: <span class="code-value">1</span>,
        <span class="code-string">"total"</span>: <span class="code-value">1</span>,
        <span class="code-string">"path"</span>: <span class="code-string">"/api/schedules"</span>
    }
}</code></pre>
                            </div>
                        </section>

                        <!-- List Events -->
                        <section id="list-events" class="doc-section">
                            <h2 class="doc-heading">List Events</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                                <code class="doc-inline-code">/api/events</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of all events.</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/events"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>Response</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code>{
    <span class="code-string">"data"</span>: [
        {
            <span class="code-string">"id"</span>: <span class="code-string">"456"</span>,
            <span class="code-string">"url"</span>: <span class="code-string">"{{ config('app.url') }}/venue-name/event-slug"</span>,
            <span class="code-string">"name"</span>: <span class="code-string">"Event Name"</span>,
            <span class="code-string">"description"</span>: <span class="code-string">"Event description"</span>,
            <span class="code-string">"starts_at"</span>: <span class="code-string">"{{ now()->format('Y-m-d') }} 19:00:00"</span>,
            <span class="code-string">"duration"</span>: <span class="code-value">3</span>,
            <span class="code-string">"venue_id"</span>: <span class="code-string">"123"</span>
        }
    ],
    <span class="code-string">"meta"</span>: {
        <span class="code-string">"current_page"</span>: <span class="code-value">1</span>,
        <span class="code-string">"from"</span>: <span class="code-value">1</span>,
        <span class="code-string">"last_page"</span>: <span class="code-value">1</span>,
        <span class="code-string">"per_page"</span>: <span class="code-value">100</span>,
        <span class="code-string">"to"</span>: <span class="code-value">1</span>,
        <span class="code-string">"total"</span>: <span class="code-value">1</span>,
        <span class="code-string">"path"</span>: <span class="code-string">"/api/events"</span>
    }
}</code></pre>
                            </div>
                        </section>

                        <!-- Create Event -->
                        <section id="create-event" class="doc-section">
                            <h2 class="doc-heading">Create Event</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                <code class="doc-inline-code">/api/events/{subdomain}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Create a new event using either JSON data or a flyer image.</p>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Schedule & Category Support</div>
                                <p>You can specify schedules and categories by ID or name:</p>
                                <ul class="doc-list mt-2">
                                    <li><strong>schedule</strong> - Assigns event to a specific subschedule</li>
                                    <li><strong>category</strong> or <strong>category_id</strong> - Sets event category</li>
                                </ul>
                                <p class="mt-2">When using names, the API will automatically find matching schedules by slug and categories by name.</p>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">JSON Request</h3>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL (JSON)</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/events/{subdomain}"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
     -H <span class="code-string">"X-Requested-With: XMLHttpRequest"</span> \
     -H <span class="code-string">"Content-Type: application/json"</span> \
     -d <span class="code-string">'{
         "name": "Event Name",
         "description": "Event description",
         "starts_at": "{{ now()->format('Y-m-d') }} 19:00:00",
         "duration": 2,
         "venue_name": "Carnegie Hall",
         "venue_address1": "123 Main St",
         "schedule": "main-schedule",
         "category": "music",
         "members": [
            {
                "name": "John Doe",
                "email": "john@example.com",
                "youtube_url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ"
            }
         ]
     }'</span></code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Flyer Image Request</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">You can also upload a flyer image along with the event data using multipart form data:</p>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL (Flyer Image)</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/events/{subdomain}"</span> \
    -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
    -H <span class="code-string">"X-Requested-With: XMLHttpRequest"</span> \
    -F <span class="code-string">"flyer_image=@/path/to/your/flyer.jpg"</span> \
    -F <span class="code-string">"name=Event Name"</span> \
    -F <span class="code-string">"description=Event description"</span> \
    -F <span class="code-string">"starts_at=2025-04-14 19:00:00"</span> \
    -F <span class="code-string">"duration=2"</span> \
    -F <span class="code-string">"venue_name=Carnegie Hall"</span> \
    -F <span class="code-string">"venue_address1=111 Main St"</span> \
    -F <span class="code-string">"schedule=main-schedule"</span> \
    -F <span class="code-string">"category=music"</span> \
    -F <span class="code-string">"members[0][name]=John Doe"</span> \
    -F <span class="code-string">"members[0][email]=john@example.com"</span></code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Response</h3>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>Response</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code>{
    <span class="code-string">"data"</span>: {
        <span class="code-string">"event_url"</span>: <span class="code-string">"https://example.com"</span>,
        <span class="code-string">"name"</span>: <span class="code-string">"Event Name"</span>,
        <span class="code-string">"description"</span>: <span class="code-string">"Event description"</span>,
        <span class="code-string">"starts_at"</span>: <span class="code-string">"{{ now()->format('Y-m-d') }} 19:00:00"</span>,
        <span class="code-string">"duration"</span>: <span class="code-value">2</span>,
        <span class="code-string">"venue_name"</span>: <span class="code-string">"Carnegie Hall"</span>,
        <span class="code-string">"venue_address1"</span>: <span class="code-string">"123 Main St"</span>,
        <span class="code-string">"schedule"</span>: <span class="code-string">"main-schedule"</span>,
        <span class="code-string">"category"</span>: <span class="code-string">"music"</span>,
        <span class="code-string">"members"</span>: {
            <span class="code-string">"123"</span>: {
                <span class="code-string">"name"</span>: <span class="code-string">"John Doe"</span>,
                <span class="code-string">"email"</span>: <span class="code-string">"john@example.com"</span>
            }
        }
    },
    <span class="code-string">"meta"</span>: {
        <span class="code-string">"message"</span>: <span class="code-string">"Event created successfully"</span>
    }
}</code></pre>
                            </div>
                        </section>

                        <!-- Create Sale -->
                        <section id="create-sale" class="doc-section">
                            <h2 class="doc-heading">Create Sale</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                <code class="doc-inline-code">/api/sales</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Create a new sale manually for an event. This allows you to programmatically create sales records with associated tickets.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Request Body</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Parameter</th>
                                            <th>Required</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">event_id</code></td>
                                            <td>Yes</td>
                                            <td>Encoded event ID</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">name</code></td>
                                            <td>Yes</td>
                                            <td>Customer name</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">email</code></td>
                                            <td>Yes</td>
                                            <td>Customer email</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">tickets</code></td>
                                            <td>Yes</td>
                                            <td>Object mapping ticket IDs to quantities</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">status</code></td>
                                            <td>No</td>
                                            <td>Sale status: unpaid, paid, cancelled, refunded, or expired (default: unpaid)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">event_date</code></td>
                                            <td>No</td>
                                            <td>Event date in Y-m-d format (default: event start date)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/sales"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
     -H <span class="code-string">"Content-Type: application/json"</span> \
     -d <span class="code-string">'{
         "event_id": "456",
         "name": "John Doe",
         "email": "john@example.com",
         "tickets": {
             "ticket_id_1": 2,
             "ticket_id_2": 1
         },
         "status": "paid",
         "event_date": "{{ now()->format('Y-m-d') }}"
     }'</span></code></pre>
                            </div>

                            <div class="doc-callout doc-callout-warning">
                                <div class="doc-callout-title">Requirements</div>
                                <ul class="doc-list mt-2">
                                    <li>Event must belong to the authenticated user</li>
                                    <li>Event must have tickets enabled and be a Pro account</li>
                                    <li>All ticket IDs must exist and belong to the event</li>
                                    <li>Ticket quantities must not exceed available tickets</li>
                                </ul>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Response</h3>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>Response</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code>{
    <span class="code-string">"data"</span>: {
        <span class="code-string">"id"</span>: <span class="code-string">"789"</span>,
        <span class="code-string">"event_id"</span>: <span class="code-string">"456"</span>,
        <span class="code-string">"name"</span>: <span class="code-string">"John Doe"</span>,
        <span class="code-string">"email"</span>: <span class="code-string">"john@example.com"</span>,
        <span class="code-string">"event_date"</span>: <span class="code-string">"{{ now()->format('Y-m-d') }}"</span>,
        <span class="code-string">"status"</span>: <span class="code-string">"paid"</span>,
        <span class="code-string">"payment_method"</span>: <span class="code-string">"cash"</span>,
        <span class="code-string">"payment_amount"</span>: <span class="code-value">50.00</span>,
        <span class="code-string">"transaction_reference"</span>: <span class="code-value">null</span>,
        <span class="code-string">"secret"</span>: <span class="code-string">"abc123def456..."</span>,
        <span class="code-string">"created_at"</span>: <span class="code-string">"{{ now()->toISOString() }}"</span>,
        <span class="code-string">"updated_at"</span>: <span class="code-string">"{{ now()->toISOString() }}"</span>,
        <span class="code-string">"tickets"</span>: [
            {
                <span class="code-string">"ticket_id"</span>: <span class="code-string">"ticket_id_1"</span>,
                <span class="code-string">"quantity"</span>: <span class="code-value">2</span>,
                <span class="code-string">"price"</span>: <span class="code-value">20.00</span>,
                <span class="code-string">"type"</span>: <span class="code-string">"General Admission"</span>
            },
            {
                <span class="code-string">"ticket_id"</span>: <span class="code-string">"ticket_id_2"</span>,
                <span class="code-string">"quantity"</span>: <span class="code-value">1</span>,
                <span class="code-string">"price"</span>: <span class="code-value">10.00</span>,
                <span class="code-string">"type"</span>: <span class="code-string">"Student"</span>
            }
        ],
        <span class="code-string">"total_quantity"</span>: <span class="code-value">3</span>
    },
    <span class="code-string">"meta"</span>: {
        <span class="code-string">"message"</span>: <span class="code-string">"Sale created successfully"</span>
    }
}</code></pre>
                            </div>
                        </section>

                        <!-- Error Handling -->
                        <section id="error-handling" class="doc-section">
                            <h2 class="doc-heading">Error Handling</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The API uses standard HTTP status codes and returns error messages in JSON format.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status Codes</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="text-green-400 font-semibold">200</span></td>
                                            <td>Success</td>
                                        </tr>
                                        <tr>
                                            <td><span class="text-green-400 font-semibold">201</span></td>
                                            <td>Created</td>
                                        </tr>
                                        <tr>
                                            <td><span class="text-red-400 font-semibold">401</span></td>
                                            <td>Unauthorized (invalid or missing API key)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="text-red-400 font-semibold">403</span></td>
                                            <td>Forbidden (insufficient permissions)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="text-red-400 font-semibold">404</span></td>
                                            <td>Not found</td>
                                        </tr>
                                        <tr>
                                            <td><span class="text-red-400 font-semibold">422</span></td>
                                            <td>Validation error</td>
                                        </tr>
                                        <tr>
                                            <td><span class="text-red-400 font-semibold">500</span></td>
                                            <td>Server error</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>Error Response</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code>{
    <span class="code-string">"data"</span>: <span class="code-value">null</span>,
    <span class="code-string">"meta"</span>: {
        <span class="code-string">"error"</span>: <span class="code-string">"Error message here"</span>
    }
}</code></pre>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
