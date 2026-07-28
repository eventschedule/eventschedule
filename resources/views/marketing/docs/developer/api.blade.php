<x-docs-page
    key="developer/api"
    title="API Reference - Event Schedule"
    description="Programmatically manage schedules and events with the Event Schedule REST API. Learn about authentication, endpoints, and rate limits."
    lede="Programmatically manage schedules and events with the Event Schedule REST API. Write operations require a Pro plan."
>
    <x-slot:toc>
        <x-doc-nav-group label="Getting Started" expanded>
            <x-doc-nav-link href="#authentication" search="authentication api key header x-api-key">Authentication</x-doc-nav-link>
            <x-doc-nav-link href="#rate-limits" search="rate limits throttle 429">Rate Limits</x-doc-nav-link>
            <x-doc-nav-link href="#response-format" search="response format json data meta error">Response Format</x-doc-nav-link>
            <x-doc-nav-link href="#pagination" search="pagination page per_page">Pagination</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Auth">
            <x-doc-nav-link href="#register" search="register post /api/register account create"><span class="api-method-dot api-method-post"></span>Register</x-doc-nav-link>
            <x-doc-nav-link href="#login" search="login post /api/login authenticate"><span class="api-method-dot api-method-post"></span>Login</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Schedules">
            <x-doc-nav-link href="#list-schedules" search="list schedules get /api/schedules filter name type"><span class="api-method-dot api-method-get"></span>List Schedules</x-doc-nav-link>
            <x-doc-nav-link href="#show-schedule" search="show schedule get /api/schedules subdomain"><span class="api-method-dot api-method-get"></span>Show Schedule</x-doc-nav-link>
            <x-doc-nav-link href="#create-schedule" search="create schedule post /api/schedules venue talent curator"><span class="api-method-dot api-method-post"></span>Create Schedule</x-doc-nav-link>
            <x-doc-nav-link href="#update-schedule" search="update schedule put /api/schedules"><span class="api-method-dot api-method-put"></span>Update Schedule</x-doc-nav-link>
            <x-doc-nav-link href="#delete-schedule" search="delete schedule /api/schedules"><span class="api-method-dot api-method-delete"></span>Delete Schedule</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Sub-Schedules">
            <x-doc-nav-link href="#list-groups" search="list sub-schedules groups get /api/schedules/groups"><span class="api-method-dot api-method-get"></span>List Sub-Schedules</x-doc-nav-link>
            <x-doc-nav-link href="#create-group" search="create sub-schedule group post /api/schedules/groups"><span class="api-method-dot api-method-post"></span>Create Sub-Schedule</x-doc-nav-link>
            <x-doc-nav-link href="#update-group" search="update sub-schedule group put /api/schedules/groups"><span class="api-method-dot api-method-put"></span>Update Sub-Schedule</x-doc-nav-link>
            <x-doc-nav-link href="#delete-group" search="delete sub-schedule group /api/schedules/groups"><span class="api-method-dot api-method-delete"></span>Delete Sub-Schedule</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Events">
            <x-doc-nav-link href="#list-events" search="list events get /api/events filter subdomain date"><span class="api-method-dot api-method-get"></span>List Events</x-doc-nav-link>
            <x-doc-nav-link href="#show-event" search="show event get /api/events detail"><span class="api-method-dot api-method-get"></span>Show Event</x-doc-nav-link>
            <x-doc-nav-link href="#create-event" search="create event post /api/events tickets agenda"><span class="api-method-dot api-method-post"></span>Create Event</x-doc-nav-link>
            <x-doc-nav-link href="#update-event" search="update event put /api/events partial"><span class="api-method-dot api-method-put"></span>Update Event</x-doc-nav-link>
            <x-doc-nav-link href="#delete-event" search="delete event /api/events"><span class="api-method-dot api-method-delete"></span>Delete Event</x-doc-nav-link>
            <x-doc-nav-link href="#upload-flyer" search="upload flyer image post /api/events/flyer multipart"><span class="api-method-dot api-method-post"></span>Upload Flyer</x-doc-nav-link>
            <x-doc-nav-link href="#list-categories" search="list categories get /api/categories"><span class="api-method-dot api-method-get"></span>List Categories</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Sales">
            <x-doc-nav-link href="#list-sales" search="list sales get /api/sales filter status email"><span class="api-method-dot api-method-get"></span>List Sales</x-doc-nav-link>
            <x-doc-nav-link href="#show-sale" search="show sale get /api/sales detail"><span class="api-method-dot api-method-get"></span>Show Sale</x-doc-nav-link>
            <x-doc-nav-link href="#create-sale" search="create sale post /api/sales tickets"><span class="api-method-dot api-method-post"></span>Create Sale</x-doc-nav-link>
            <x-doc-nav-link href="#update-sale" search="update sale status put /api/sales mark_paid refund cancel"><span class="api-method-dot api-method-put"></span>Update Sale Status</x-doc-nav-link>
            <x-doc-nav-link href="#delete-sale" search="delete sale /api/sales"><span class="api-method-dot api-method-delete"></span>Delete Sale</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Feedback">
            <x-doc-nav-link href="#list-feedback" search="list feedback ratings reviews get /api/feedback stars comments"><span class="api-method-dot api-method-get"></span>List Feedback</x-doc-nav-link>
            <x-doc-nav-link href="#list-fan-content" search="list fan content get /api/fan-content comments photos videos approved"><span class="api-method-dot api-method-get"></span>List Fan Content</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Reference">
            <x-doc-nav-link href="#error-handling" search="error handling status codes 401 403 404 422 429 500">Error Handling</x-doc-nav-link>
            <x-doc-nav-link href="#see-also" search="see also resources links openapi">See Also</x-doc-nav-link>
        </x-doc-nav-group>
        <div class="border-t border-gray-200 dark:border-white/10 mt-4 pt-4">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Related</div>
        <a href="{{ route('marketing.docs.developer.webhooks') }}" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Webhooks</a>
        </div>    </x-slot:toc>

    {{-- Page-local layout only. The sidebar, its search, the mobile drawer and
         the syntax palette all moved to the shared shell / docs.css; what is
         left is the two-panel endpoint grid and the dark bleed behind the code
         column, which is specific to this page. --}}
    <style {!! nonce_attr() !!}>
        .api-endpoint-row { margin-bottom: 0; }
        @media (min-width: 1024px) {
            .api-endpoint-row { display: grid; grid-template-columns: 1fr 380px; gap: 2rem; align-items: start; }
            .api-endpoint-code { position: sticky; top: 5rem; }
            .api-endpoint-section { padding-bottom: 2.5rem; margin-bottom: 2.5rem; }
            .api-endpoint-code { border-top: 1px solid rgba(255,255,255,0.08); padding-top: 1.5rem; }
        }
        @media (min-width: 1280px) {
            .api-endpoint-row { grid-template-columns: 1fr 480px; gap: 2.5rem; }
        }
        .api-endpoint-desc { min-width: 0; }
        .api-method-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; margin-right: 0.5rem; flex-shrink: 0; }
        .api-method-get { background: #3b82f6; }
        .api-method-post { background: #10b981; }
        .api-method-put { background: #f59e0b; }
        .api-method-delete { background: #ef4444; }
        @media print {
            .api-endpoint-row { display: block !important; }
        }
    </style>

    <div class="api-content-wrapper relative">
        <div class="relative z-[1]">
            <!-- Authentication -->
            <section id="authentication" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                            </svg>
                            Authentication
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Most API endpoints require authentication via an API key in the <code class="doc-inline-code">X-API-Key</code> header. You can get an API key by:</p>
                        <ul class="doc-list mb-6">
                            <li>Using the <a href="#register" class="doc-link">Register</a> or <a href="#login" class="doc-link">Login</a> endpoints (for AI agents)</li>
                            <li>Generating one from your <a href="{{ route('marketing.docs.account_settings') }}#api" class="doc-link">account settings</a> (for manual use)</li>
                        </ul>
                        <div class="doc-callout doc-callout-info">
                            <div class="doc-callout-title">API Key Security</div>
                            <p>Keep your API key secure and never expose it in client-side code. API keys expire after 1 year. Login generates a new key each time, replacing any existing key.</p>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL Example</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/schedules"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Rate Limits -->
            <section id="rate-limits" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Rate Limits
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">API requests are rate limited per IP address:</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Operation Type</th><th>Limit</th><th>HTTP Methods</th></tr></thead>
                                <tbody>
                                    <tr><td>Read operations</td><td>300 requests/minute</td><td>GET</td></tr>
                                    <tr><td>Write operations</td><td>30 requests/minute</td><td>POST, PUT, DELETE</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">Auth endpoints (register, login) have separate per-endpoint rate limits. When rate limited, the API returns a <code class="doc-inline-code">429</code> status code.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Rate Limit Response (429)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"error"</span>: <span class="code-string">"Rate limit exceeded"</span>
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Response Format -->
            <section id="response-format" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            Response Format
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Successful responses wrap results in a <code class="doc-inline-code">data</code> property. List endpoints include a <code class="doc-inline-code">meta</code> object with pagination. Error responses use an <code class="doc-inline-code">error</code> property.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Success Response</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: [...],
        <span class="code-string">"meta"</span>: {
            <span class="code-string">"current_page"</span>: <span class="code-value">1</span>,
            <span class="code-string">"total"</span>: <span class="code-value">50</span>
        }
    }</code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Error Response</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"error"</span>: <span class="code-string">"Validation failed"</span>,
        <span class="code-string">"errors"</span>: {
            <span class="code-string">"name"</span>: [<span class="code-string">"The name field is required."</span>]
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Pagination -->
            <section id="pagination" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                            Pagination
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">List endpoints support pagination through query parameters:</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Default</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">page</code></td><td>1</td><td>Page number to retrieve</td></tr>
                                    <tr><td><code class="doc-inline-code">per_page</code></td><td>100</td><td>Number of items per page (max: 500)</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/events?page=2&per_page=50"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Register -->
            <section id="register" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                            </svg>
                            Register
                        </h2>
                        @if(config('app.hosted'))
                        <p class="text-gray-600 dark:text-gray-300 mb-4">Registration is a two-step process: first send a verification code, then register with it.</p>
                        <h3 class="doc-subheading">Step 1: Send Verification Code</h3>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/register/send-code</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">No authentication required. Rate limited to 5 codes per email per hour.</p>
                        <h3 class="doc-subheading">Step 2: Register</h3>
                        @else
                        <p class="text-gray-600 dark:text-gray-300 mb-4">Create a new account and receive an API key.</p>
                        @endif
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/register</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">No authentication required. Rate limited to 3 registrations per IP per hour.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">name</code></td><td>Yes</td><td>Your display name</td></tr>
                                    <tr><td><code class="doc-inline-code">email</code></td><td>Yes</td><td>Email address</td></tr>
                                    <tr><td><code class="doc-inline-code">password</code></td><td>Yes</td><td>Password (min 8 characters)</td></tr>
                                    @if(config('app.hosted'))
                                    <tr><td><code class="doc-inline-code">verification_code</code></td><td>Yes</td><td>6-digit code from Step 1</td></tr>
                                    @endif
                                    <tr><td><code class="doc-inline-code">timezone</code></td><td>No</td><td>Timezone (default: America/New_York)</td></tr>
                                    <tr><td><code class="doc-inline-code">language_code</code></td><td>No</td><td>Language code (default: en)</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        @if(config('app.hosted'))
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Step 1: Send Code</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/register/send-code"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{"email": "user@example.com"}'</span></code></pre>
                        </div>
                        @endif
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (201)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"api_key"</span>: <span class="code-string">"your_new_api_key"</span>,
            <span class="code-string">"api_key_expires_at"</span>: <span class="code-string">"2027-02-28T00:00:00Z"</span>,
            <span class="code-string">"user"</span>: {
                <span class="code-string">"id"</span>: <span class="code-string">"abc123"</span>,
                <span class="code-string">"name"</span>: <span class="code-string">"Your Name"</span>,
                <span class="code-string">"email"</span>: <span class="code-string">"user@example.com"</span>
            }
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Login -->
            <section id="login" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Login
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/login</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">No authentication required. Returns a new API key on success.</p>
                        <div class="doc-callout doc-callout-warning">
                            <div class="doc-callout-title">Important</div>
                            <p>Login generates a new API key each time, replacing any existing key. Store the key securely and avoid calling login repeatedly. Accounts with two-factor authentication must generate API keys from the web UI.</p>
                        </div>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">email</code></td><td>Yes</td><td>Email address</td></tr>
                                    <tr><td><code class="doc-inline-code">password</code></td><td>Yes</td><td>Password</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/login"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{"email": "user@example.com", "password": "your_password"}'</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"api_key"</span>: <span class="code-string">"your_new_api_key"</span>,
            <span class="code-string">"api_key_expires_at"</span>: <span class="code-string">"2027-02-28T00:00:00Z"</span>,
            <span class="code-string">"user"</span>: {
                <span class="code-string">"id"</span>: <span class="code-string">"abc123"</span>,
                <span class="code-string">"name"</span>: <span class="code-string">"Your Name"</span>,
                <span class="code-string">"email"</span>: <span class="code-string">"user@example.com"</span>
            }
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
            <!-- List Schedules -->
            <section id="list-schedules" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            List Schedules
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/schedules</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of your schedules. Supports filtering:</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">name</code></td><td>Filter by schedule name (partial match)</td></tr>
                                    <tr><td><code class="doc-inline-code">type</code></td><td>Filter by type: <code class="doc-inline-code">venue</code>, <code class="doc-inline-code">talent</code>, or <code class="doc-inline-code">curator</code></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/schedules?type=venue"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: [
            {
                <span class="code-string">"id"</span>: <span class="code-string">"abc123"</span>,
                <span class="code-string">"subdomain"</span>: <span class="code-string">"my-venue"</span>,
                <span class="code-string">"name"</span>: <span class="code-string">"My Venue"</span>,
                <span class="code-string">"type"</span>: <span class="code-string">"venue"</span>,
                <span class="code-string">"email"</span>: <span class="code-string">"info@myvenue.com"</span>,
                <span class="code-string">"timezone"</span>: <span class="code-string">"America/New_York"</span>,
                ...
            }
        ],
        <span class="code-string">"meta"</span>: { <span class="code-string">"current_page"</span>: <span class="code-value">1</span>, <span class="code-string">"total"</span>: <span class="code-value">5</span> }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Show Schedule -->
            <section id="show-schedule" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Show Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a single schedule by subdomain, including its sub-schedules. Requires owner or admin access.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/schedules/my-venue"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"id"</span>: <span class="code-string">"abc123"</span>,
            <span class="code-string">"subdomain"</span>: <span class="code-string">"my-venue"</span>,
            <span class="code-string">"name"</span>: <span class="code-string">"My Venue"</span>,
            <span class="code-string">"type"</span>: <span class="code-string">"venue"</span>,
            <span class="code-string">"groups"</span>: [
                { <span class="code-string">"id"</span>: <span class="code-string">"def456"</span>, <span class="code-string">"name"</span>: <span class="code-string">"Main Stage"</span>, <span class="code-string">"slug"</span>: <span class="code-string">"main-stage"</span> }
            ],
            ...
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Create Schedule -->
            <section id="create-schedule" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Create Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/schedules</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Create a new schedule. New accounts in hosted mode automatically get a Pro trial.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">name</code></td><td>Yes</td><td>Schedule name (max 255 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">type</code></td><td>Yes</td><td>Schedule type: <code class="doc-inline-code">venue</code>, <code class="doc-inline-code">talent</code>, or <code class="doc-inline-code">curator</code></td></tr>
                                    <tr><td><code class="doc-inline-code">email</code></td><td>No</td><td>Contact email</td></tr>
                                    <tr><td><code class="doc-inline-code">description</code></td><td>No</td><td>Markdown description</td></tr>
                                    <tr><td><code class="doc-inline-code">timezone</code></td><td>No</td><td>Timezone (e.g., America/New_York)</td></tr>
                                    <tr><td><code class="doc-inline-code">language_code</code></td><td>No</td><td>Language code (e.g., en, es, fr)</td></tr>
                                    <tr><td><code class="doc-inline-code">website</code></td><td>No</td><td>Website URL</td></tr>
                                    <tr><td><code class="doc-inline-code">address1</code>, <code class="doc-inline-code">city</code>, <code class="doc-inline-code">state</code>, <code class="doc-inline-code">postal_code</code>, <code class="doc-inline-code">country_code</code></td><td>No</td><td>Venue address fields (for venue type)</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/schedules"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{"name": "My Venue", "type": "venue", "city": "New York"}'</span></code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Update Schedule -->
            <section id="update-schedule" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Update Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-yellow-600 text-white px-2 py-1 rounded text-sm font-medium">PUT</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Update a schedule. Only include fields you want to change. Requires Pro plan and owner or admin access. Subdomain cannot be changed via API.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X PUT <span class="code-string">"{{ config('app.url') }}/api/schedules/my-venue"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{"name": "Updated Name", "description": "New description"}'</span></code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Delete Schedule -->
            <section id="delete-schedule" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Delete Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-red-600 text-white px-2 py-1 rounded text-sm font-medium">DELETE</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Permanently delete a schedule and all associated data. Requires schedule owner access (not just admin).</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X DELETE <span class="code-string">"{{ config('app.url') }}/api/schedules/my-venue"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"message"</span>: <span class="code-string">"Schedule deleted successfully"</span>
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- List Sub-Schedules -->
            <section id="list-groups" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            List Sub-Schedules
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}/groups</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">List all sub-schedules for a schedule. Returns id, name, slug, and color for each.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/schedules/my-venue/groups"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: [
            {
                <span class="code-string">"id"</span>: <span class="code-string">"def456"</span>,
                <span class="code-string">"name"</span>: <span class="code-string">"Main Stage"</span>,
                <span class="code-string">"slug"</span>: <span class="code-string">"main-stage"</span>,
                <span class="code-string">"color"</span>: <span class="code-string">"#FF5733"</span>
            }
        ]
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Create Sub-Schedule -->
            <section id="create-group" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Create Sub-Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}/groups</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Create a sub-schedule within a schedule. Requires Pro plan. Slug is auto-generated from the name. Names are auto-translated if the schedule language is not English.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">name</code></td><td>Yes</td><td>Sub-schedule name</td></tr>
                                    <tr><td><code class="doc-inline-code">color</code></td><td>No</td><td>Display color (e.g., #FF5733)</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/schedules/my-venue/groups"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{"name": "Main Stage", "color": "#FF5733"}'</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (201)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"id"</span>: <span class="code-string">"def456"</span>,
            <span class="code-string">"name"</span>: <span class="code-string">"Main Stage"</span>,
            <span class="code-string">"slug"</span>: <span class="code-string">"main-stage"</span>,
            <span class="code-string">"color"</span>: <span class="code-string">"#FF5733"</span>
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Update Sub-Schedule -->
            <section id="update-group" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Update Sub-Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-yellow-600 text-white px-2 py-1 rounded text-sm font-medium">PUT</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}/groups/{group_id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Update a sub-schedule's name or color. Requires Pro plan.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X PUT <span class="code-string">"{{ config('app.url') }}/api/schedules/my-venue/groups/def456"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{"name": "VIP Stage", "color": "#3B82F6"}'</span></code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Delete Sub-Schedule -->
            <section id="delete-group" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Delete Sub-Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-red-600 text-white px-2 py-1 rounded text-sm font-medium">DELETE</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}/groups/{group_id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Delete a sub-schedule. Events assigned to this sub-schedule will have their sub-schedule reference cleared. Requires Pro plan.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X DELETE <span class="code-string">"{{ config('app.url') }}/api/schedules/my-venue/groups/def456"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"message"</span>: <span class="code-string">"Sub-schedule deleted successfully"</span>
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
            <!-- List Events -->
            <section id="list-events" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            List Events
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/events</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of your events, sorted by start date (newest first). Supports filtering:</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">subdomain</code></td><td>Filter events by schedule subdomain</td></tr>
                                    <tr><td><code class="doc-inline-code">starts_after</code></td><td>Events starting after this date (Y-m-d)</td></tr>
                                    <tr><td><code class="doc-inline-code">starts_before</code></td><td>Events starting before this date (Y-m-d)</td></tr>
                                    <tr><td><code class="doc-inline-code">venue_id</code></td><td>Filter by venue (encoded venue schedule ID)</td></tr>
                                    <tr><td><code class="doc-inline-code">category_id</code></td><td>Filter by category ID</td></tr>
                                    <tr><td><code class="doc-inline-code">name</code></td><td>Filter by event name (partial match)</td></tr>
                                    <tr><td><code class="doc-inline-code">schedule_type</code></td><td>Filter by type: <code class="doc-inline-code">single</code> or <code class="doc-inline-code">recurring</code></td></tr>
                                    <tr><td><code class="doc-inline-code">tickets_enabled</code></td><td>Filter by whether tickets are enabled (boolean)</td></tr>
                                    <tr><td><code class="doc-inline-code">rsvp_enabled</code></td><td>Filter by whether RSVP/registration is enabled (boolean)</td></tr>
                                    <tr><td><code class="doc-inline-code">group_id</code></td><td>Filter by sub-schedule (encoded sub-schedule ID)</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/events?subdomain=my-venue&starts_after=2025-01-01"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: [
            {
                <span class="code-string">"id"</span>: <span class="code-string">"evt123"</span>,
                <span class="code-string">"name"</span>: <span class="code-string">"Jazz Night"</span>,
                <span class="code-string">"starts_at"</span>: <span class="code-string">"2025-03-15 20:00:00"</span>,
                <span class="code-string">"duration"</span>: <span class="code-value">3</span>,
                <span class="code-string">"tickets_enabled"</span>: <span class="code-value">true</span>,
                <span class="code-string">"rsvp_enabled"</span>: <span class="code-value">false</span>,
                ...
            }
        ],
        <span class="code-string">"meta"</span>: { <span class="code-string">"current_page"</span>: <span class="code-value">1</span>, <span class="code-string">"total"</span>: <span class="code-value">25</span> }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Show Event -->
            <section id="show-event" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Show Event
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/events/{id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a single event by encoded ID, including tickets, members, and agenda parts.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/events/evt123"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"id"</span>: <span class="code-string">"evt123"</span>,
            <span class="code-string">"name"</span>: <span class="code-string">"Jazz Night"</span>,
            <span class="code-string">"starts_at"</span>: <span class="code-string">"2025-03-15 20:00:00"</span>,
            <span class="code-string">"duration"</span>: <span class="code-value">3</span>,
            <span class="code-string">"tickets"</span>: [
                { <span class="code-string">"id"</span>: <span class="code-string">"tkt1"</span>, <span class="code-string">"type"</span>: <span class="code-string">"General"</span>, <span class="code-string">"price"</span>: <span class="code-value">25</span>, <span class="code-string">"quantity"</span>: <span class="code-value">100</span> }
            ],
            <span class="code-string">"event_parts"</span>: [
                { <span class="code-string">"name"</span>: <span class="code-string">"Opening Act"</span>, <span class="code-string">"start_time"</span>: <span class="code-string">"20:00"</span> }
            ],
            ...
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Create Event -->
            <section id="create-event" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Create Event
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/events/{subdomain}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Create a new event on a schedule. Requires Pro plan and owner or admin access.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">name</code></td><td>Yes</td><td>Event name</td></tr>
                                    <tr><td><code class="doc-inline-code">starts_at</code></td><td>Yes</td><td>Start date/time in your timezone (Y-m-d H:i:s)</td></tr>
                                    <tr><td><code class="doc-inline-code">duration</code></td><td>No</td><td>Duration in hours (0 to 24)</td></tr>
                                    <tr><td><code class="doc-inline-code">description</code></td><td>No</td><td>Full description (Markdown supported, max 10000 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">short_description</code></td><td>No</td><td>Short description (max 500 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">event_url</code></td><td>No</td><td>Event or livestream URL</td></tr>
                                    <tr><td><code class="doc-inline-code">registration_url</code></td><td>No</td><td>External registration URL</td></tr>
                                    <tr><td><code class="doc-inline-code">event_password</code></td><td>No</td><td>Password to protect the event page</td></tr>
                                    <tr><td><code class="doc-inline-code">is_private</code></td><td>No</td><td>Make event private (hidden from calendar)</td></tr>
                                    <tr><td><code class="doc-inline-code">rsvp_enabled</code></td><td>No</td><td>Enable free RSVP/registration (boolean)</td></tr>
                                    <tr><td><code class="doc-inline-code">rsvp_limit</code></td><td>No</td><td>Maximum number of registrations (integer, min 1)</td></tr>
                                    <tr><td><code class="doc-inline-code">category_id</code></td><td>No</td><td>Category ID (see <a href="#list-categories" class="doc-link">List Categories</a>)</td></tr>
                                    <tr><td><code class="doc-inline-code">category</code></td><td>No</td><td>Category name (alternative to category_id)</td></tr>
                                    <tr><td><code class="doc-inline-code">schedule</code></td><td>No</td><td>Sub-schedule slug to assign the event to</td></tr>
                                    <tr><td><code class="doc-inline-code">schedule_type</code></td><td>No</td><td><code class="doc-inline-code">single</code> or <code class="doc-inline-code">recurring</code></td></tr>
                                    <tr><td><code class="doc-inline-code">recurring_frequency</code></td><td>No</td><td>For recurring: daily, weekly, every_n_weeks, monthly_date, monthly_weekday, yearly</td></tr>
                                    <tr><td><code class="doc-inline-code">recurring_interval</code></td><td>No</td><td>Week interval for every_n_weeks frequency (min 2)</td></tr>
                                    <tr><td><code class="doc-inline-code">recurring_end_type</code></td><td>No</td><td>How recurrence ends: never, on_date, or after_events</td></tr>
                                    <tr><td><code class="doc-inline-code">recurring_end_value</code></td><td>No</td><td>End date (Y-m-d) or count, based on recurring_end_type</td></tr>
                                    <tr><td><code class="doc-inline-code">tickets_enabled</code></td><td>No</td><td>Enable ticketing (boolean)</td></tr>
                                    <tr><td><code class="doc-inline-code">ticket_currency_code</code></td><td>No</td><td>3-letter ISO currency code (e.g., USD)</td></tr>
                                    <tr><td><code class="doc-inline-code">payment_method</code></td><td>No</td><td>Payment method: stripe, invoiceninja, payment_url, or manual</td></tr>
                                    <tr><td><code class="doc-inline-code">payment_instructions</code></td><td>No</td><td>Payment instructions (max 5000 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">tickets</code></td><td>No</td><td>Array of ticket types: [{type, quantity, price, description}]</td></tr>
                                    <tr><td><code class="doc-inline-code">event_parts</code></td><td>No</td><td>Agenda parts: [{name, description, start_time, end_time}]</td></tr>
                                    <tr><td><code class="doc-inline-code">members</code></td><td>No</td><td>Array of performers: [{name, email}]</td></tr>
                                    <tr><td><code class="doc-inline-code">venue_id</code></td><td>No</td><td>Encoded venue schedule ID</td></tr>
                                    <tr><td><code class="doc-inline-code">venue_name</code></td><td>No</td><td>Venue name (with venue_address1 for auto-matching)</td></tr>
                                    <tr><td><code class="doc-inline-code">venue_address1</code></td><td>No</td><td>Venue address (with venue_name for auto-matching)</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/events/my-venue"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{
             "name": "Jazz Night",
             "starts_at": "{{ now()->addDays(7)->format('Y-m-d') }} 20:00:00",
             "duration": 3,
             "description": "A wonderful evening of jazz music.",
             "tickets_enabled": true,
             "tickets": [
                 {"type": "General Admission", "price": 25, "quantity": 100},
                 {"type": "VIP", "price": 50, "quantity": 20}
             ],
             "event_parts": [
                 {"name": "Opening Act", "start_time": "20:00", "end_time": "20:45"},
                 {"name": "Main Performance", "start_time": "21:00", "end_time": "23:00"}
             ]
         }'</span></code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Update Event -->
            <section id="update-event" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Update Event
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-yellow-600 text-white px-2 py-1 rounded text-sm font-medium">PUT</span>
                            <code class="doc-inline-code">/api/events/{id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Update an event. Accepts the same parameters as <a href="#create-event" class="doc-link">Create Event</a>. Supports partial updates - only include the fields you want to change. Recurring configuration, tickets, and agenda parts are preserved when not included in the request. Requires Pro plan.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X PUT <span class="code-string">"{{ config('app.url') }}/api/events/evt123"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{"name": "Updated Jazz Night", "duration": 4}'</span></code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Delete Event -->
            <section id="delete-event" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Delete Event
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-red-600 text-white px-2 py-1 rounded text-sm font-medium">DELETE</span>
                            <code class="doc-inline-code">/api/events/{id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Permanently delete an event. This also removes it from Google Calendar and CalDAV if synced.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X DELETE <span class="code-string">"{{ config('app.url') }}/api/events/evt123"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"message"</span>: <span class="code-string">"Event deleted successfully"</span>
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Upload Flyer -->
            <section id="upload-flyer" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            Upload Flyer
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/events/flyer/{event_id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Upload a flyer image for an event. Send as multipart form data with a <code class="doc-inline-code">flyer_image</code> field.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/events/flyer/evt123"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -F <span class="code-string">"flyer_image=@/path/to/flyer.jpg"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: { ... },
        <span class="code-string">"meta"</span>: {
            <span class="code-string">"message"</span>: <span class="code-string">"Flyer uploaded successfully"</span>
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- List Categories -->
            <section id="list-categories" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            List Categories
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/categories</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns all available event categories with their IDs and names. Use the <code class="doc-inline-code">id</code> when creating or updating events.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/categories"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: [
            {<span class="code-string">"id"</span>: <span class="code-value">1</span>, <span class="code-string">"name"</span>: <span class="code-string">"Art & Culture"</span>},
            {<span class="code-string">"id"</span>: <span class="code-value">2</span>, <span class="code-string">"name"</span>: <span class="code-string">"Business Networking"</span>},
            {<span class="code-string">"id"</span>: <span class="code-value">3</span>, <span class="code-string">"name"</span>: <span class="code-string">"Community"</span>},
            {<span class="code-string">"id"</span>: <span class="code-value">4</span>, <span class="code-string">"name"</span>: <span class="code-string">"Concerts"</span>},
            ...
        ]
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
            <!-- List Sales -->
            <section id="list-sales" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            List Sales
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/sales</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of sales for events you own or administer. Supports filtering:</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">event_id</code></td><td>Filter by event (encoded event ID)</td></tr>
                                    <tr><td><code class="doc-inline-code">subdomain</code></td><td>Filter by schedule subdomain</td></tr>
                                    <tr><td><code class="doc-inline-code">status</code></td><td>Filter by status: unpaid, paid, cancelled, refunded, or expired</td></tr>
                                    <tr><td><code class="doc-inline-code">email</code></td><td>Filter by buyer email (exact match)</td></tr>
                                    <tr><td><code class="doc-inline-code">event_date</code></td><td>Filter by event date (Y-m-d)</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/sales?status=paid&subdomain=my-venue"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: [
            {
                <span class="code-string">"id"</span>: <span class="code-string">"sale123"</span>,
                <span class="code-string">"event_name"</span>: <span class="code-string">"Jazz Night"</span>,
                <span class="code-string">"name"</span>: <span class="code-string">"John Doe"</span>,
                <span class="code-string">"email"</span>: <span class="code-string">"john@example.com"</span>,
                <span class="code-string">"status"</span>: <span class="code-string">"paid"</span>,
                <span class="code-string">"payment_amount"</span>: <span class="code-value">50</span>,
                <span class="code-string">"tickets"</span>: [
                    { <span class="code-string">"type"</span>: <span class="code-string">"General"</span>, <span class="code-string">"quantity"</span>: <span class="code-value">2</span>, <span class="code-string">"price"</span>: <span class="code-value">25</span> }
                ],
                ...
            }
        ],
        <span class="code-string">"meta"</span>: { <span class="code-string">"current_page"</span>: <span class="code-value">1</span>, <span class="code-string">"total"</span>: <span class="code-value">12</span> }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Show Sale -->
            <section id="show-sale" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Show Sale
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/sales/{id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a single sale by encoded ID, including ticket details.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/sales/sale123"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"id"</span>: <span class="code-string">"sale123"</span>,
            <span class="code-string">"event_id"</span>: <span class="code-string">"evt123"</span>,
            <span class="code-string">"event_name"</span>: <span class="code-string">"Jazz Night"</span>,
            <span class="code-string">"name"</span>: <span class="code-string">"John Doe"</span>,
            <span class="code-string">"email"</span>: <span class="code-string">"john@example.com"</span>,
            <span class="code-string">"status"</span>: <span class="code-string">"paid"</span>,
            <span class="code-string">"payment_amount"</span>: <span class="code-value">50</span>,
            <span class="code-string">"total_quantity"</span>: <span class="code-value">2</span>,
            <span class="code-string">"tickets"</span>: [
                { <span class="code-string">"type"</span>: <span class="code-string">"General"</span>, <span class="code-string">"quantity"</span>: <span class="code-value">2</span>, <span class="code-string">"price"</span>: <span class="code-value">25</span> }
            ]
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Create Sale -->
            <section id="create-sale" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Create Sale
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/sales</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Create a new sale manually for an event. Sales are created as unpaid (free tickets are auto-marked as paid).</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">event_id</code></td><td>Yes</td><td>Encoded event ID</td></tr>
                                    <tr><td><code class="doc-inline-code">name</code></td><td>Yes</td><td>Customer name</td></tr>
                                    <tr><td><code class="doc-inline-code">email</code></td><td>Yes</td><td>Customer email</td></tr>
                                    <tr><td><code class="doc-inline-code">tickets</code></td><td>Yes</td><td>Object mapping ticket identifiers to quantities. Keys can be encoded ticket IDs or ticket type names.</td></tr>
                                    <tr><td><code class="doc-inline-code">event_date</code></td><td>No</td><td>Event date in Y-m-d format (default: event start date)</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/sales"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{
             "event_id": "evt123",
             "name": "John Doe",
             "email": "john@example.com",
             "tickets": {"General Admission": 2}
         }'</span></code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Update Sale -->
            <section id="update-sale" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Update Sale Status
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-yellow-600 text-white px-2 py-1 rounded text-sm font-medium">PUT</span>
                            <code class="doc-inline-code">/api/sales/{id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Perform a status action on a sale. Available actions depend on the current status.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Action</th><th>From Status</th><th>To Status</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">mark_paid</code></td><td>unpaid</td><td>paid</td></tr>
                                    <tr><td><code class="doc-inline-code">refund</code></td><td>paid</td><td>refunded</td></tr>
                                    <tr><td><code class="doc-inline-code">cancel</code></td><td>unpaid, paid</td><td>cancelled</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X PUT <span class="code-string">"{{ config('app.url') }}/api/sales/sale123"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{"action": "mark_paid"}'</span></code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Delete Sale -->
            <section id="delete-sale" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Delete Sale
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-red-600 text-white px-2 py-1 rounded text-sm font-medium">DELETE</span>
                            <code class="doc-inline-code">/api/sales/{id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Soft-delete a sale. The sale will no longer appear in listings.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X DELETE <span class="code-string">"{{ config('app.url') }}/api/sales/sale123"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"message"</span>: <span class="code-string">"Sale deleted successfully"</span>
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- List Feedback -->
            <section id="list-feedback" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                            List Feedback
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/feedback</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of post-event feedback (star ratings and comments) for events on schedules you own or administer. Requires a Pro plan. Supports filtering:</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">event_id</code></td><td>Filter by event (encoded event ID)</td></tr>
                                    <tr><td><code class="doc-inline-code">subdomain</code></td><td>Filter by schedule subdomain</td></tr>
                                    <tr><td><code class="doc-inline-code">event_date</code></td><td>Filter by event date (Y-m-d)</td></tr>
                                    <tr><td><code class="doc-inline-code">min_rating</code></td><td>Only return ratings of at least this value (1-5)</td></tr>
                                    <tr><td><code class="doc-inline-code">from</code></td><td>Only feedback submitted on or after this date (Y-m-d)</td></tr>
                                    <tr><td><code class="doc-inline-code">to</code></td><td>Only feedback submitted on or before this date (Y-m-d)</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/feedback?min_rating=4&subdomain=my-venue"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: [
            {
                <span class="code-string">"id"</span>: <span class="code-string">"fb123"</span>,
                <span class="code-string">"event_id"</span>: <span class="code-string">"ev456"</span>,
                <span class="code-string">"event_name"</span>: <span class="code-string">"Jazz Night"</span>,
                <span class="code-string">"event_date"</span>: <span class="code-string">"2026-07-10"</span>,
                <span class="code-string">"rating"</span>: <span class="code-value">5</span>,
                <span class="code-string">"comment"</span>: <span class="code-string">"Best night out all year"</span>,
                <span class="code-string">"attendee_name"</span>: <span class="code-string">"Alex Attendee"</span>,
                <span class="code-string">"attendee_email"</span>: <span class="code-string">"alex@example.com"</span>,
                <span class="code-string">"created_at"</span>: <span class="code-string">"2026-07-11T09:12:00+00:00"</span>
            }
        ],
        <span class="code-string">"meta"</span>: { <span class="code-string">"current_page"</span>: <span class="code-value">1</span>, <span class="code-string">"total"</span>: <span class="code-value">8</span> }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- List Fan Content -->
            <section id="list-fan-content" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                            </svg>
                            List Fan Content
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/fan-content</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns fan comments, photos and videos submitted on your events, newest first. Approved items only by default, which is what you want when displaying them on an external site. Submitter email addresses are never included.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Each kind of submission has its own <code class="doc-inline-code">id</code> sequence, so an <code class="doc-inline-code">id</code> is only unique within a <code class="doc-inline-code">type</code>. Key on the two together when storing rows from this feed.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">type</code></td><td>Limit to one kind: comment, photo, or video</td></tr>
                                    <tr><td><code class="doc-inline-code">event_id</code></td><td>Filter by event (encoded event ID)</td></tr>
                                    <tr><td><code class="doc-inline-code">subdomain</code></td><td>Filter by schedule subdomain</td></tr>
                                    <tr><td><code class="doc-inline-code">event_date</code></td><td>Filter by event date (Y-m-d)</td></tr>
                                    <tr><td><code class="doc-inline-code">is_approved</code></td><td>Defaults to true. Pass 0 to read the pending moderation queue instead</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/fan-content?type=photo&subdomain=my-venue"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: [
            {
                <span class="code-string">"id"</span>: <span class="code-string">"ph123"</span>,
                <span class="code-string">"type"</span>: <span class="code-string">"photo"</span>,
                <span class="code-string">"event_id"</span>: <span class="code-string">"ev456"</span>,
                <span class="code-string">"event_name"</span>: <span class="code-string">"Jazz Night"</span>,
                <span class="code-string">"event_date"</span>: <span class="code-string">"2026-07-10"</span>,
                <span class="code-string">"submitted_by"</span>: <span class="code-string">"Dana Guest"</span>,
                <span class="code-string">"is_guest_submission"</span>: <span class="code-value">true</span>,
                <span class="code-string">"is_approved"</span>: <span class="code-value">true</span>,
                <span class="code-string">"photo_url"</span>: <span class="code-string">"https://.../crowd.jpg"</span>,
                <span class="code-string">"created_at"</span>: <span class="code-string">"2026-07-11T09:12:00+00:00"</span>
            }
        ],
        <span class="code-string">"meta"</span>: { <span class="code-string">"current_page"</span>: <span class="code-value">1</span>, <span class="code-string">"total"</span>: <span class="code-value">24</span> }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Error Handling -->
            <section id="error-handling" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            Error Handling
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">The API uses standard HTTP status codes and returns error messages in JSON format.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Code</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><span class="text-green-400 font-semibold">200</span></td><td>Success</td></tr>
                                    <tr><td><span class="text-green-400 font-semibold">201</span></td><td>Created</td></tr>
                                    <tr><td><span class="text-red-400 font-semibold">401</span></td><td>Unauthorized (invalid or missing API key)</td></tr>
                                    <tr><td><span class="text-red-400 font-semibold">403</span></td><td>Forbidden (insufficient permissions or Pro required)</td></tr>
                                    <tr><td><span class="text-red-400 font-semibold">404</span></td><td>Not found</td></tr>
                                    <tr><td><span class="text-red-400 font-semibold">422</span></td><td>Validation error (includes field-level errors)</td></tr>
                                    <tr><td><span class="text-red-400 font-semibold">429</span></td><td>Rate limit exceeded</td></tr>
                                    <tr><td><span class="text-red-400 font-semibold">500</span></td><td>Server error</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Validation Error (422)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"error"</span>: <span class="code-string">"Validation failed"</span>,
        <span class="code-string">"errors"</span>: {
            <span class="code-string">"name"</span>: [<span class="code-string">"The name field is required."</span>],
            <span class="code-string">"starts_at"</span>: [<span class="code-string">"The starts at must match the format Y-m-d H:i:s."</span>]
        }
    }</code></pre>
                        </div>
                    </div>
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
                    <li><a href="/api/openapi.json" class="doc-link">OpenAPI Specification</a> - Machine-readable API spec for AI agents and code generators</li>
                    <li><a href="{{ route('marketing.docs.account_settings') }}#api" class="doc-link">Account Settings</a> - Enable API and manage your API key</li>
                    <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - Understand event fields and details</li>
                    <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - Understand tickets and sales</li>
                </ul>
            </section>
        </div>
    </div></x-docs-page>
