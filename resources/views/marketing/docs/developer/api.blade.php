<x-docs-page
    key="developer/api"
    title="API Reference: Schedules, Events, Sales - Event Schedule"
    description="Manage schedules, events, sales and refunds with the Event Schedule REST API: authentication, rate limits, pagination and every endpoint, with examples."
    plan="pro"
    lede="A JSON REST API for your schedules, events, sales, refunds and fan content. API access is a Pro feature: on the hosted service the schedules you read and write have to be on a Pro or Enterprise plan, apart from the few endpoints listed under Authentication. A selfhosted install counts as Enterprise, so nothing here is held back by plan."
>
    <x-slot:toc>
        <x-doc-nav-group label="Getting Started" expanded>
            <x-doc-nav-link href="#authentication" search="authentication api key header x-api-key">Authentication</x-doc-nav-link>
            <x-doc-nav-link href="#rate-limits" search="rate limits throttle 429">Rate Limits</x-doc-nav-link>
            <x-doc-nav-link href="#response-format" search="response format json data meta error accept header html record ids dates times utc iso 8601">Response Format</x-doc-nav-link>
            <x-doc-nav-link href="#pagination" search="pagination page per_page">Pagination</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Auth">
            <x-doc-nav-link href="#register" search="register post /api/register account create"><span class="api-method-dot api-method-post"></span>Register</x-doc-nav-link>
            <x-doc-nav-link href="#login" search="login post /api/login authenticate"><span class="api-method-dot api-method-post"></span>Login</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Schedules">
            <x-doc-nav-link href="#list-schedules" search="list schedules get /api/schedules filter name type"><span class="api-method-dot api-method-get"></span>List Schedules</x-doc-nav-link>
            <x-doc-nav-link href="#show-schedule" search="show schedule get /api/schedules subdomain schedule object fields"><span class="api-method-dot api-method-get"></span>Show Schedule</x-doc-nav-link>
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
            <x-doc-nav-link href="#show-event" search="show event get /api/events detail event object fields tickets members schedules"><span class="api-method-dot api-method-get"></span>Show Event</x-doc-nav-link>
            <x-doc-nav-link href="#create-event" search="create event post /api/events tickets agenda recurrence visibility venue members payment method daily cap"><span class="api-method-dot api-method-post"></span>Create Event</x-doc-nav-link>
            <x-doc-nav-link href="#update-event" search="update event put /api/events partial replaces removes promo codes passes venue members"><span class="api-method-dot api-method-put"></span>Update Event</x-doc-nav-link>
            <x-doc-nav-link href="#delete-event" search="delete event /api/events sales deleted appointment"><span class="api-method-dot api-method-delete"></span>Delete Event</x-doc-nav-link>
            <x-doc-nav-link href="#cancel-event" search="cancel event post /api/events/cancel call off notify attendees sales is_cancelled"><span class="api-method-dot api-method-post"></span>Cancel Event</x-doc-nav-link>
            <x-doc-nav-link href="#restore-event" search="restore event post /api/events/restore undo cancellation"><span class="api-method-dot api-method-post"></span>Restore Event</x-doc-nav-link>
            <x-doc-nav-link href="#upload-flyer" search="upload flyer image post /api/events/flyer multipart"><span class="api-method-dot api-method-post"></span>Upload Flyer</x-doc-nav-link>
            <x-doc-nav-link href="#list-categories" search="list categories get /api/categories"><span class="api-method-dot api-method-get"></span>List Categories</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Sales">
            <x-doc-nav-link href="#list-sales" search="list sales get /api/sales filter status email"><span class="api-method-dot api-method-get"></span>List Sales</x-doc-nav-link>
            <x-doc-nav-link href="#show-sale" search="show sale get /api/sales detail sale object fields group order secret amount_mismatch"><span class="api-method-dot api-method-get"></span>Show Sale</x-doc-nav-link>
            <x-doc-nav-link href="#create-sale" search="create sale post /api/sales tickets"><span class="api-method-dot api-method-post"></span>Create Sale</x-doc-nav-link>
            <x-doc-nav-link href="#update-sale" search="update sale status put /api/sales mark_paid refund partial refund cancel idempotency_key"><span class="api-method-dot api-method-put"></span>Update Sale Status</x-doc-nav-link>
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
        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Related</div>
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
        /* Method pills. The colours live here rather than as bg-* utilities so the
           green and amber ones can be dark enough to clear AA against white text,
           which the 600 shades in the marketing bundle do not. */
        .api-method-pill { color: #fff; }
        .api-method-pill-get { background: #2563eb; }
        .api-method-pill-post { background: #15803d; }
        .api-method-pill-put { background: #a16207; }
        .api-method-pill-delete { background: #dc2626; }
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                            </svg>
                            Authentication
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Every endpoint except <a href="#register" class="doc-link">Register</a> and <a href="#login" class="doc-link">Login</a> authenticates with an API key sent in the <code class="doc-inline-code">X-API-Key</code> header. There are two ways to get one:</p>
                        <ul class="doc-list mb-6">
                            <li>Open <strong class="text-gray-900 dark:text-white">Settings</strong> in the admin panel and choose the <strong class="text-gray-900 dark:text-white">Developers</strong> tab. Under <strong class="text-gray-900 dark:text-white">API Settings</strong>, switch on <strong class="text-gray-900 dark:text-white">Enable API Access</strong> and press <strong class="text-gray-900 dark:text-white">Save</strong>. The key is shown once, beside a copy button, so copy it before you leave the page. See <a href="{{ route('marketing.docs.account_settings') }}#api" class="doc-link">Account Settings</a>.</li>
                            <li>Call the <a href="#register" class="doc-link">Register</a> or <a href="#login" class="doc-link">Login</a> endpoints, which return a key in the response body (useful for AI agents and scripted setup).</li>
                        </ul>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">A key belongs to a <em>user account</em>, not to one schedule. It can reach every schedule where you are the owner or an admin, and nothing else. Followers and members cannot be used to authorise API calls.</p>
                        <h3 class="doc-subheading">Plan requirement</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">On the hosted service, a schedule must be on a Pro or Enterprise plan for the API to see it. The schedule, event, sale and feedback lists leave out anything that belongs only to free schedules, and single-record reads and writes on a free schedule return <code class="doc-inline-code">403 API usage is limited to Pro accounts</code>. A few endpoints carry no plan check at all: <a href="#create-schedule" class="doc-link">Create Schedule</a>, so a new account can bootstrap; <a href="#delete-schedule" class="doc-link">Delete Schedule</a>, so an owner can always retire one; <a href="#create-sale" class="doc-link">Create Sale</a>, so a schedule can record a sale it took elsewhere; both <a href="#list-categories" class="doc-link">List Categories</a> routes; and <a href="#list-fan-content" class="doc-link">List Fan Content</a>. A new schedule starts on the Free plan with no trial. Selfhosted installs resolve to Enterprise, so every endpoint is available there.</p>
                        <h3 class="doc-subheading">Key lifetime and rotation</h3>
                        <ul class="doc-list mb-6">
                            <li>A key expires one year after it is issued. After that every request returns <code class="doc-inline-code">401 API key expired</code>. <strong class="text-gray-900 dark:text-white">API Settings</strong> shows the expiry date under the key, and says so once the key has expired.</li>
                            <li>Keys are stored hashed, so a lost key cannot be recovered. To rotate one, switch <strong class="text-gray-900 dark:text-white">Enable API Access</strong> off and press <strong class="text-gray-900 dark:text-white">Save</strong>, then switch it on and save again. The first save deletes the old key at once, after asking you to confirm, and the second issues a new one.</li>
                            <li>Ten consecutive requests with the same invalid key block that key for 15 minutes with <code class="doc-inline-code">423 API key temporarily blocked</code>.</li>
                            <li>A request with no <code class="doc-inline-code">X-API-Key</code> header returns <code class="doc-inline-code">401 API key is required</code>, and an unknown key <code class="doc-inline-code">401 Invalid API key</code>.</li>
                        </ul>
                        <div class="doc-callout doc-callout-warning">
                            <div class="doc-callout-title">Keep the key server-side</div>
                            <p>An API key carries full owner and admin rights over your schedules, including sales data and buyer email addresses. Never ship it in client-side code, a mobile app bundle or a public repository.</p>
                        </div>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL Example</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/schedules"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Accept: application/json"</span></code></pre>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Rate Limits -->
            <section id="rate-limits" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Rate Limits
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Authenticated requests are counted per IP address, in separate read and write buckets. Every key calling from the same address shares the same two buckets, and a request that fails authentication is not counted:</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Operation Type</th><th>Limit</th><th>HTTP Methods</th></tr></thead>
                                <tbody>
                                    <tr><td>Read operations</td><td>300 requests/minute</td><td>GET</td></tr>
                                    <tr><td>Write operations</td><td>30 requests/minute</td><td>POST, PUT, DELETE</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Each bucket counts in a fixed one-minute window that opens with its first counted request. When that minute is up the count starts again from zero, so a client that makes no more than 300 reads and 30 writes in any one window is never refused, however long it runs.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6"><a href="#create-event" class="doc-link">Create Event</a> carries a second throttle of 30 requests per minute on top of the write bucket. It is counted per account and per schedule rather than per address, so a bulk import should pace itself well below that.</p>
                        <h3 class="doc-subheading">Unauthenticated endpoints</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">The auth endpoints are limited separately, because they run before any key exists:</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Endpoint</th><th>Limit</th><th>Counted per</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">/api/register/send-code</code></td><td>5 codes per hour</td><td>Email address</td></tr>
                                    <tr><td><code class="doc-inline-code">/api/register</code></td><td>3 registrations per hour</td><td>IP address</td></tr>
                                    <tr><td><code class="doc-inline-code">/api/login</code></td><td>5 failed attempts per 15 minutes</td><td>IP address</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">The read and write buckets and the three endpoints above all answer <code class="doc-inline-code">429</code> with an <code class="doc-inline-code">error</code> message when the limit is hit. A <code class="doc-inline-code">429</code> from a read or write bucket also carries a <code class="doc-inline-code">Retry-After</code> header with the number of seconds until that bucket's window resets. The three auth endpoints send no rate limit headers, so back off on the status code there.</p>
                        <p class="text-gray-600 dark:text-gray-300 mt-6">The Create Event throttle answers differently, because it is the framework's own: its <code class="doc-inline-code">429</code> carries <code class="doc-inline-code">Retry-After</code>, <code class="doc-inline-code">X-RateLimit-Limit</code> and <code class="doc-inline-code">X-RateLimit-Remaining</code> headers, and its body holds a <code class="doc-inline-code">message</code> in place of <code class="doc-inline-code">error</code>. See <a href="#response-format" class="doc-link">Response Format</a> for why that request needs an <code class="doc-inline-code">Accept</code> header.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            Response Format
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Every endpoint answers in JSON. Successful responses wrap the result in a <code class="doc-inline-code">data</code> property: an object for single-record endpoints, an array for list endpoints. List endpoints add a <code class="doc-inline-code">meta</code> object with the pagination counters. Event and sale writes and Upload Flyer put a confirmation in <code class="doc-inline-code">meta.message</code>, while the delete endpoints return theirs as <code class="doc-inline-code">data.message</code>.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Failures return an <code class="doc-inline-code">error</code> string. A validation failure adds an <code class="doc-inline-code">errors</code> object keyed by field name, each holding an array of messages.</p>
                        <h3 class="doc-subheading" id="accept-header">Ask for JSON</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Send <code class="doc-inline-code">Accept: application/json</code> with every request. A failure raised outside an endpoint is rendered by the framework, which picks its format from that header. Without it, the <a href="#create-event" class="doc-link">Create Event</a> throttle, an unknown URL, an unknown <code class="doc-inline-code">venue_id</code> and a server error come back as HTML pages, and the daily cap on new events comes back as a redirect. With it they are JSON, though the framework's own replies name the reason <code class="doc-inline-code">message</code>, not <code class="doc-inline-code">error</code>.</p>
                        <h3 class="doc-subheading" id="record-ids">Record IDs</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Schedules, events, sub-schedules, tickets and sales are all identified by an opaque encoded string such as <code class="doc-inline-code">"w4K8xU"</code>, never by the raw database number. Pass the same string back exactly as you received it. The examples on this page use readable stand-ins such as <code class="doc-inline-code">"evt123"</code>. Category IDs are the one exception: they are plain integers.</p>
                        <h3 class="doc-subheading" id="dates-and-times">Dates and times</h3>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Value</th><th>Format</th></tr></thead>
                                <tbody>
                                    <tr><td>An event's <code class="doc-inline-code">starts_at</code>, sent or returned</td><td>UTC, <code class="doc-inline-code">Y-m-d H:i:s</code>, with no offset suffix. The schedule's timezone only decides how that instant is shown on the guest page.</td></tr>
                                    <tr><td><code class="doc-inline-code">starts_after</code>, <code class="doc-inline-code">starts_before</code></td><td>UTC calendar dates, <code class="doc-inline-code">Y-m-d</code></td></tr>
                                    <tr><td><code class="doc-inline-code">event_date</code> on sales, feedback and fan content</td><td><code class="doc-inline-code">Y-m-d</code>, the date of the occurrence. For a sale it is the calendar date in the schedule's own timezone.</td></tr>
                                    <tr><td><code class="doc-inline-code">created_at</code>, <code class="doc-inline-code">updated_at</code>, <code class="doc-inline-code">api_key_expires_at</code> and a returned ticket's <code class="doc-inline-code">sales_start_at</code> and <code class="doc-inline-code">sales_end_at</code></td><td>ISO 8601 with an offset, such as <code class="doc-inline-code">2026-07-11T09:12:00+00:00</code></td></tr>
                                    <tr><td>A ticket's <code class="doc-inline-code">sales_start_at</code> and <code class="doc-inline-code">sales_end_at</code>, when you send them</td><td>UTC, <code class="doc-inline-code">Y-m-d H:i:s</code>. An offset in the value is not applied.</td></tr>
                                    <tr><td>An agenda part's <code class="doc-inline-code">start_time</code> and <code class="doc-inline-code">end_time</code></td><td>Plain text of up to 10 characters such as <code class="doc-inline-code">"20:00"</code>, stored and returned as sent</td></tr>
                                </tbody>
                            </table>
                        </div>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                            Pagination
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Every list endpoint takes the same two query parameters:</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Default</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">page</code></td><td>1</td><td>Page number to retrieve</td></tr>
                                    <tr><td><code class="doc-inline-code">per_page</code></td><td>100</td><td>Items per page, maximum 500. Events, sales, feedback and fan content reject a larger value with a <code class="doc-inline-code">422</code>; schedules clamp it to 500.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <h3 class="doc-subheading">The meta object</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Every list response returns the same seven counters. Keep requesting <code class="doc-inline-code">page + 1</code> until it equals <code class="doc-inline-code">last_page</code>.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Field</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">current_page</code></td><td>The page you just received</td></tr>
                                    <tr><td><code class="doc-inline-code">last_page</code></td><td>The final page number for this query</td></tr>
                                    <tr><td><code class="doc-inline-code">per_page</code></td><td>Page size actually applied</td></tr>
                                    <tr><td><code class="doc-inline-code">total</code></td><td>Total matching records across all pages</td></tr>
                                    <tr><td><code class="doc-inline-code">from</code>, <code class="doc-inline-code">to</code></td><td>1-based index of the first and last record on this page, or null when the page is empty</td></tr>
                                    <tr><td><code class="doc-inline-code">path</code></td><td>The request URL without its query string</td></tr>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                            </svg>
                            Register
                        </h2>
                        @if(config('app.hosted'))
                        <p class="text-gray-600 dark:text-gray-300 mb-4">Registration takes two steps: send a verification code to the email address, then register with the code.</p>
                        <h3 class="doc-subheading">Step 1: Send Verification Code</h3>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-post px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/register/send-code</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">No authentication required. Takes a single <code class="doc-inline-code">email</code> parameter and emails a 6-digit code that is valid for 10 minutes, answering <code class="doc-inline-code">200</code> with a confirmation in <code class="doc-inline-code">data.message</code>. Rate limited to 5 codes per email per hour. An address that already belongs to a full account is rejected with a <code class="doc-inline-code">422</code>.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">A new code replaces the one before it, and a code can be tried once: registering with a wrong <code class="doc-inline-code">verification_code</code> uses it up, so request another.</p>
                        <h3 class="doc-subheading">Step 2: Register</h3>
                        @else
                        <p class="text-gray-600 dark:text-gray-300 mb-4">Create a new account and receive an API key. No verification code is involved on a selfhosted install, and <code class="doc-inline-code">/api/register/send-code</code> returns a <code class="doc-inline-code">400</code> there.</p>
                        @endif
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-post px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/register</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">No authentication required. Rate limited to 3 registrations per IP per hour. On success it returns <code class="doc-inline-code">201</code> with an API key that is valid for one year, and the account's email is treated as verified.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">name</code></td><td>Yes</td><td>Your display name (max 255 characters)</td></tr>
                                    @if(config('app.hosted'))
                                    <tr><td><code class="doc-inline-code">email</code></td><td>Yes</td><td>Email address (max 255 characters). Disposable-mail domains and <code class="doc-inline-code">@example.com</code> addresses are refused, here and in Step 1.</td></tr>
                                    @else
                                    <tr><td><code class="doc-inline-code">email</code></td><td>Yes</td><td>Email address (max 255 characters)</td></tr>
                                    @endif
                                    <tr><td><code class="doc-inline-code">password</code></td><td>Yes</td><td>Password (min 8 characters)</td></tr>
                                    @if(config('app.hosted'))
                                    <tr><td><code class="doc-inline-code">verification_code</code></td><td>Yes</td><td>6-digit code from Step 1</td></tr>
                                    @endif
                                    <tr><td><code class="doc-inline-code">timezone</code></td><td>No</td><td>IANA timezone name (default: America/New_York, which is also what an unrecognised name falls back to)</td></tr>
                                    <tr><td><code class="doc-inline-code">language_code</code></td><td>No</td><td>One of the supported interface languages (default: en)</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mt-6">@if(config('app.hosted'))Both steps watch @else The endpoint also watches @endif a hidden <code class="doc-inline-code">website</code> honeypot field. Leave it out entirely: sending any value in it returns <code class="doc-inline-code">422 Invalid request</code>.</p>
                        @if(! config('app.hosted'))
                        <div class="doc-callout doc-callout-warning">
                            <div class="doc-callout-title">Registration closes after the first account</div>
                            <p>On a selfhosted install, once any account exists this endpoint returns <code class="doc-inline-code">403 Registration is closed</code>, unless the install has opted in to open sign-ups with <code class="doc-inline-code">ALLOW_REGISTRATION</code>. An existing account can still get a key from <a href="#login" class="doc-link">Login</a> or from <strong class="text-gray-900 dark:text-white">Settings</strong>.</p>
                        </div>
                        @endif
                    </div>
                    <div class="api-endpoint-code">
                        @if(config('app.hosted'))
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Step 1: Send Code</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/register/send-code"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{"email": "you@yourdomain.com"}'</span></code></pre>
                        </div>
                        @endif
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>@if(config('app.hosted'))Step 2: Register @else cURL @endif</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/register"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -H <span class="code-string">"Accept: application/json"</span> \
         -d <span class="code-string">'{
             "name": "Your Name",
             "email": "you@yourdomain.com",
             "password": "a_long_password"@if(config('app.hosted')),
             "verification_code": "123456"@endif

         }'</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (201)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"api_key"</span>: <span class="code-string">"your_new_api_key"</span>,
            <span class="code-string">"api_key_expires_at"</span>: <span class="code-string">"2027-02-28T00:00:00+00:00"</span>,
            <span class="code-string">"user"</span>: {
                <span class="code-string">"id"</span>: <span class="code-string">"abc123"</span>,
                <span class="code-string">"name"</span>: <span class="code-string">"Your Name"</span>,
                <span class="code-string">"email"</span>: <span class="code-string">"you@yourdomain.com"</span>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Login
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-post px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/login</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">No authentication required. Exchanges an email and password for an API key valid for one year.</p>
                        <div class="doc-callout doc-callout-info">
                            <div class="doc-callout-title">Login issues a key only when you do not already have one</div>
                            <p>This is not a session endpoint and it will not hand you a fresh key on demand. If the account already has an unexpired key, login returns <code class="doc-inline-code">409</code> and issues nothing, so store the key from the first call. To replace a key you have lost, rotate it under <strong class="text-gray-900 dark:text-white">Settings</strong>, <strong class="text-gray-900 dark:text-white">Developers</strong>, as described in <a href="#authentication" class="doc-link">Authentication</a>. An expired key does not block login: the call issues a new one.</p>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">Two other refusals to handle: an account with two-factor authentication enabled returns <code class="doc-inline-code">403</code> and must generate its key from <strong class="text-gray-900 dark:text-white">Settings</strong> instead, and a wrong email or password returns <code class="doc-inline-code">401</code> and counts toward the 5-per-15-minute limit. A successful login clears that count.</p>
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
         -d <span class="code-string">'{"email": "you@yourdomain.com", "password": "your_password"}'</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"api_key"</span>: <span class="code-string">"your_new_api_key"</span>,
            <span class="code-string">"api_key_expires_at"</span>: <span class="code-string">"2027-02-28T00:00:00+00:00"</span>,
            <span class="code-string">"user"</span>: {
                <span class="code-string">"id"</span>: <span class="code-string">"abc123"</span>,
                <span class="code-string">"name"</span>: <span class="code-string">"Your Name"</span>,
                <span class="code-string">"email"</span>: <span class="code-string">"you@yourdomain.com"</span>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            List Schedules
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-get px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/schedules</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of the schedules where you are the owner or an admin. Deleted schedules are excluded, and on the hosted service so are schedules that are not on a Pro or Enterprise plan. Each row carries the schedule's sub-schedules in a <code class="doc-inline-code">groups</code> array.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">subdomain</code></td><td>Filter by exact subdomain</td></tr>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Show Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-get px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a single schedule by subdomain, including its sub-schedules in a <code class="doc-inline-code">groups</code> array. You must be the owner or an admin of it, otherwise the response is <code class="doc-inline-code">404</code>. A schedule that is not on a Pro or Enterprise plan returns <code class="doc-inline-code">403</code>.</p>
                        <h3 class="doc-subheading" id="schedule-object">The schedule object</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">List, Show, Create and Update Schedule return this shape. A field with nothing stored is <code class="doc-inline-code">null</code>.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Field</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">id</code></td><td>Encoded schedule ID</td></tr>
                                    <tr><td><code class="doc-inline-code">subdomain</code></td><td>The name every <code class="doc-inline-code">{subdomain}</code> path takes</td></tr>
                                    <tr><td><code class="doc-inline-code">url</code></td><td>Address of the schedule's public page. An empty string until the schedule is verified.</td></tr>
                                    <tr><td><code class="doc-inline-code">type</code></td><td><code class="doc-inline-code">venue</code>, <code class="doc-inline-code">talent</code> or <code class="doc-inline-code">curator</code></td></tr>
                                    <tr><td><code class="doc-inline-code">name</code>, <code class="doc-inline-code">email</code>, <code class="doc-inline-code">phone</code>, <code class="doc-inline-code">website</code></td><td>Name and contact details</td></tr>
                                    <tr><td><code class="doc-inline-code">show_phone</code></td><td><code class="doc-inline-code">1</code> when the phone number is shown on the public page, <code class="doc-inline-code">0</code> when it is not. Read only, as is <code class="doc-inline-code">phone</code>.</td></tr>
                                    <tr><td><code class="doc-inline-code">description</code>, <code class="doc-inline-code">short_description</code></td><td>Markdown description and one-line summary</td></tr>
                                    <tr><td><code class="doc-inline-code">timezone</code>, <code class="doc-inline-code">language_code</code></td><td>IANA timezone name and language code</td></tr>
                                    <tr><td><code class="doc-inline-code">profile_image_url</code></td><td>URL of the profile image, or <code class="doc-inline-code">null</code>. Read only.</td></tr>
                                    <tr><td><code class="doc-inline-code">address1</code>, <code class="doc-inline-code">city</code>, <code class="doc-inline-code">state</code>, <code class="doc-inline-code">postal_code</code>, <code class="doc-inline-code">country_code</code></td><td>Address</td></tr>
                                    <tr><td><code class="doc-inline-code">created_at</code>, <code class="doc-inline-code">updated_at</code></td><td>ISO 8601 timestamps</td></tr>
                                    <tr><td><code class="doc-inline-code">groups</code></td><td>The schedule's sub-schedules, each with <code class="doc-inline-code">id</code>, <code class="doc-inline-code">name</code>, <code class="doc-inline-code">slug</code> and <code class="doc-inline-code">color</code></td></tr>
                                </tbody>
                            </table>
                        </div>
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
            <span class="code-string">"email"</span>: <span class="code-string">"info@myvenue.com"</span>,
            <span class="code-string">"timezone"</span>: <span class="code-string">"America/New_York"</span>,
            <span class="code-string">"created_at"</span>: <span class="code-string">"2026-03-01T12:00:00+00:00"</span>,
            <span class="code-string">"groups"</span>: [
                {
                    <span class="code-string">"id"</span>: <span class="code-string">"def456"</span>,
                    <span class="code-string">"name"</span>: <span class="code-string">"Main Stage"</span>,
                    <span class="code-string">"slug"</span>: <span class="code-string">"main-stage"</span>,
                    <span class="code-string">"color"</span>: <span class="code-string">"#FF5733"</span>
                }
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Create Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-post px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/schedules</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Create a new schedule. There is no plan gate here, so a new account can bootstrap itself, but the schedule starts on the Free plan with no trial and most other endpoints need it on Pro or Enterprise, so on the hosted service subscribe before you start pushing events. You are attached to the new schedule as its owner, and it becomes your default schedule if you had none.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">name</code></td><td>Yes</td><td>Schedule name (max 255 characters). The subdomain is generated from it and cannot be set through the API.</td></tr>
                                    <tr><td><code class="doc-inline-code">type</code></td><td>Yes</td><td>Schedule type: <code class="doc-inline-code">venue</code>, <code class="doc-inline-code">talent</code>, or <code class="doc-inline-code">curator</code></td></tr>
                                    <tr><td><code class="doc-inline-code">email</code></td><td>No</td><td>Contact email (max 255 characters). On the hosted service a schedule made without one takes your account's own address, which is what verifies it (see below). On a selfhosted install it stays empty.</td></tr>
                                    <tr><td><code class="doc-inline-code">description</code></td><td>No</td><td>Markdown description (max 10,000 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">short_description</code></td><td>No</td><td>One-line summary (max 200 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">timezone</code></td><td>No</td><td>IANA timezone name (defaults to your account timezone). A name that is not a timezone returns a <code class="doc-inline-code">422</code>.</td></tr>
                                    <tr><td><code class="doc-inline-code">language_code</code></td><td>No</td><td>Supported language code such as en, es, fr (defaults to your account language)</td></tr>
                                    <tr><td><code class="doc-inline-code">website</code></td><td>No</td><td>Website URL (max 255 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">address1</code>, <code class="doc-inline-code">city</code>, <code class="doc-inline-code">state</code>, <code class="doc-inline-code">postal_code</code>, <code class="doc-inline-code">country_code</code></td><td>No</td><td>Address fields, used for venue schedules (max 255 characters each, 20 for <code class="doc-inline-code">postal_code</code>). Send <code class="doc-inline-code">country_code</code> as a two-letter ISO code.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <h3 class="doc-subheading">What comes back</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">The reply is <code class="doc-inline-code">201</code>, and <code class="doc-inline-code">data</code> holds <a href="#schedule-object" class="doc-link">the schedule object</a>, generated <code class="doc-inline-code">subdomain</code> included. This is the one place the object is returned for a schedule on the Free plan, so you can read what you made. <a href="#show-schedule" class="doc-link">Show Schedule</a> answers <code class="doc-inline-code">403</code> for it and <a href="#list-schedules" class="doc-link">List Schedules</a> leaves it out until it is on Pro or Enterprise.</p>
                        <h3 class="doc-subheading">Hosted limits and verification</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">On the hosted service one account may own up to 50 schedules. Beyond that the endpoint returns a <code class="doc-inline-code">422</code>.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">On the hosted service a schedule is not public until its contact email or phone number is verified: until then its pages answer visitors with a 404, so search engines never see them. Leave <code class="doc-inline-code">email</code> out, or send your account's own address, and the schedule shares your account's verification; any other address is sent a verification link. A selfhosted install treats every new schedule as verified.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/schedules"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{
             "name": "My Venue",
             "type": "venue",
             "email": "you@yourdomain.com",
             "city": "New York"
         }'</span></code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Update Schedule -->
            <section id="update-schedule" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Update Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-put px-2 py-1 rounded text-sm font-medium">PUT</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Update a schedule. Include only the fields you want to change; anything you omit is left alone. Takes the same fields as <a href="#create-schedule" class="doc-link">Create Schedule</a> apart from <code class="doc-inline-code">type</code>: neither the schedule type nor the subdomain can be changed through the API, and any other field in the request is ignored. Requires owner or admin access and a Pro or Enterprise plan. Returns <a href="#schedule-object" class="doc-link">the schedule object</a>.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">On the hosted service, changing <code class="doc-inline-code">email</code> clears the schedule's email verification and sends a verification link to the new address. Unless its phone number is verified, the schedule's pages stop being public until that link is followed.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Branding, images, layout and integrations are not exposed here. Edit those in the admin panel, on the <strong class="text-gray-900 dark:text-white">Style</strong>, <strong class="text-gray-900 dark:text-white">Settings</strong> and <strong class="text-gray-900 dark:text-white">Integrations</strong> tabs of the schedule form.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Delete Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-delete px-2 py-1 rounded text-sm font-medium">DELETE</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Retire a schedule. Requires owner access but no plan: an admin gets a <code class="doc-inline-code">404</code>. There is no undo through the API.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">The schedule is flagged as deleted and stops appearing anywhere, and the call also:</p>
                        <ul class="doc-list mb-6">
                            <li>Deletes its profile, header and background images from storage</li>
                            <li>Deletes its analytics history (page views, referrers and appearances)</li>
                            <li>Tears down its Google Calendar and Outlook sync subscriptions</li>
                            <li>Cancels any running boost campaign and refunds it where money is owed</li>
                            <li>Emails the schedule's members to tell them it was deleted</li>
                            <li>Frees its subdomain for anyone to register, keeping the old name on record so a platform admin can restore the schedule</li>
                        </ul>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Events are not swept up automatically. The exception is a talent schedule: an event whose only member was that schedule is deleted with it, so nothing is left orphaned.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">A schedule with a paid plan has its subscription cancelled first, at once and not at the end of the billing period. If that cancellation fails the reply is <code class="doc-inline-code">502</code> and nothing is deleted, so the call is safe to repeat.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            List Sub-Schedules
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-get px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}/groups</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">List every sub-schedule on a schedule, returning <code class="doc-inline-code">id</code>, <code class="doc-inline-code">name</code>, <code class="doc-inline-code">slug</code> and <code class="doc-inline-code">color</code> for each. Requires owner or admin access and a Pro or Enterprise plan. The response is not paginated.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Sub-schedules group and colour-code events so visitors can filter your calendar. They do not control who can see an event: use the visibility flags on <a href="#create-event" class="doc-link">Create Event</a> for that.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Create Sub-Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-post px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}/groups</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Create a sub-schedule on a schedule. Requires owner or admin access and a Pro or Enterprise plan. The slug is generated from the name and is what you pass as the <code class="doc-inline-code">schedule</code> parameter when creating an event.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">name</code></td><td>Yes</td><td>Sub-schedule name (max 255 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">color</code></td><td>No</td><td>Display colour as a hex value, for example #FF5733 (max 50 characters)</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mt-6">If the schedule has a translation language set that differs from its own language, the name is machine-translated into it and the slug is built from the translated name, so read the slug back from the response rather than deriving it yourself.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Update Sub-Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-put px-2 py-1 rounded text-sm font-medium">PUT</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}/groups/{group_id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Update a sub-schedule's name or colour; send only the one you want to change. Requires owner or admin access and a Pro or Enterprise plan. Changing the name regenerates the slug, which changes the value events must pass in <code class="doc-inline-code">schedule</code>, so re-read it from the response.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Delete Sub-Schedule
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-delete px-2 py-1 rounded text-sm font-medium">DELETE</span>
                            <code class="doc-inline-code">/api/schedules/{subdomain}/groups/{group_id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Delete a sub-schedule. Events assigned to it are kept and simply lose the assignment. Requires owner or admin access and a Pro or Enterprise plan. Pass the encoded sub-schedule <code class="doc-inline-code">id</code> from <a href="#list-groups" class="doc-link">List Sub-Schedules</a>.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            List Events
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-get px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/events</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of events on the schedules where you are the owner or an admin, newest start date first. On the hosted service an event is only listed if at least one of its schedules is on a Pro or Enterprise plan. Appointment bookings are never returned here; they are not calendar events.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Drafts, internal, unlisted and cancelled events are all included, so check <code class="doc-inline-code">is_draft</code>, <code class="doc-inline-code">is_internal</code>, <code class="doc-inline-code">is_private</code> and <code class="doc-inline-code">is_cancelled</code> before republishing a row on a public site. Each row is <a href="#event-object" class="doc-link">the event object</a>.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">subdomain</code></td><td>Filter events by schedule subdomain</td></tr>
                                    <tr><td><code class="doc-inline-code">starts_after</code></td><td>Events starting on or after this UTC date (Y-m-d)</td></tr>
                                    <tr><td><code class="doc-inline-code">starts_before</code></td><td>Events starting on or before this UTC date (Y-m-d)</td></tr>
                                    <tr><td><code class="doc-inline-code">venue_id</code></td><td>Filter by venue (encoded venue schedule ID)</td></tr>
                                    <tr><td><code class="doc-inline-code">category_id</code></td><td>Filter by category ID (integer, see <a href="#list-categories" class="doc-link">List Categories</a>)</td></tr>
                                    <tr><td><code class="doc-inline-code">name</code></td><td>Filter by event name (partial match)</td></tr>
                                    <tr><td><code class="doc-inline-code">schedule_type</code></td><td>Filter by type: <code class="doc-inline-code">single</code> or <code class="doc-inline-code">recurring</code></td></tr>
                                    <tr><td><code class="doc-inline-code">tickets_enabled</code></td><td>Filter by whether tickets are enabled (boolean)</td></tr>
                                    <tr><td><code class="doc-inline-code">rsvp_enabled</code></td><td>Filter by whether RSVP/registration is enabled (boolean)</td></tr>
                                    <tr><td><code class="doc-inline-code">group_id</code></td><td>Filter by sub-schedule (encoded sub-schedule ID)</td></tr>
                                    <tr><td><code class="doc-inline-code">is_cancelled</code></td><td><code class="doc-inline-code">true</code> for cancelled events only, <code class="doc-inline-code">false</code> to leave them out. Without it both are listed.</td></tr>
                                    <tr><td><code class="doc-inline-code">external_id</code></td><td>The event your own system knows by this id, matched exactly. With <code class="doc-inline-code">subdomain</code> it is one event or none. See <a href="#event-external-id" class="doc-link">External ID</a>.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="doc-callout doc-callout-info">
                            <div class="doc-callout-title">All times are UTC</div>
                            <p>The API reads and writes <code class="doc-inline-code">starts_at</code> in UTC, in <code class="doc-inline-code">Y-m-d H:i:s</code> format with no offset suffix. The schedule's own timezone only controls how that instant is displayed on the guest page, so convert on your side before filtering or creating. <a href="#dates-and-times" class="doc-link">Dates and times</a> lists every format.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Show Event
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-get px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/events/{id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a single event by its encoded ID, including its ticket types, add-ons, members, agenda parts, venue, recurring configuration and visibility flags. Requires owner or admin access on one of the event's schedules, and a Pro or Enterprise plan. An appointment booking, which <a href="#list-events" class="doc-link">List Events</a> leaves out, can be read here by its ID.</p>
                        <h3 class="doc-subheading" id="event-object">The event object</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">List, Show, Create, Update, Cancel and Restore Event and Upload Flyer return this shape, and so do the <code class="doc-inline-code">event.*</code> <a href="{{ route('marketing.docs.developer.webhooks') }}#payload" class="doc-link">webhooks</a>. A field with nothing stored is <code class="doc-inline-code">null</code>, except the few marked as present only in some cases.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Field</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">id</code>, <code class="doc-inline-code">url</code></td><td>Encoded event ID and the address of the event's public page</td></tr>
                                    <tr><td><code class="doc-inline-code">name</code>, <code class="doc-inline-code">short_description</code>, <code class="doc-inline-code">description</code></td><td>Name, short summary and Markdown description</td></tr>
                                    <tr><td><code class="doc-inline-code">starts_at</code>, <code class="doc-inline-code">duration</code></td><td>Start in UTC (<code class="doc-inline-code">Y-m-d H:i:s</code>) and length in hours</td></tr>
                                    <tr><td><code class="doc-inline-code">category_id</code>, <code class="doc-inline-code">category_name</code>, <code class="doc-inline-code">category_color</code></td><td>The category's integer ID, with its name and colour as the schedule has them</td></tr>
                                    <tr><td><code class="doc-inline-code">is_draft</code>, <code class="doc-inline-code">is_private</code>, <code class="doc-inline-code">is_internal</code></td><td>Visibility flags, see <a href="#create-event" class="doc-link">Create Event</a></td></tr>
                                    <tr><td><code class="doc-inline-code">external_id</code></td><td>The id your own system gave the event, or <code class="doc-inline-code">null</code>. See <a href="#event-external-id" class="doc-link">External ID</a>.</td></tr>
                                    <tr><td><code class="doc-inline-code">is_cancelled</code>, <code class="doc-inline-code">cancelled_at</code></td><td>Whether the event is cancelled, and when (ISO 8601, <code class="doc-inline-code">null</code> otherwise). Read-only: change it with <a href="#cancel-event" class="doc-link">Cancel Event</a> and <a href="#restore-event" class="doc-link">Restore Event</a>. Sending back the value you read is accepted on create and update; a different one is a <code class="doc-inline-code">422</code>.</td></tr>
                                    <tr><td><code class="doc-inline-code">is_password_protected</code></td><td>Whether the event page asks for a password. The password itself is never returned.</td></tr>
                                    <tr><td><code class="doc-inline-code">event_url</code>, <code class="doc-inline-code">registration_url</code></td><td>The online or external event link, and the external registration link</td></tr>
                                    <tr><td><code class="doc-inline-code">venue_id</code>, <code class="doc-inline-code">venue_name</code>, <code class="doc-inline-code">venue_address1</code>, <code class="doc-inline-code">venue_subdomain</code></td><td>The event's venue schedule</td></tr>
                                    <tr><td><code class="doc-inline-code">flyer_image_url</code></td><td>URL of the flyer image</td></tr>
                                    <tr><td><code class="doc-inline-code">schedule_type</code></td><td><code class="doc-inline-code">single</code> or <code class="doc-inline-code">recurring</code></td></tr>
                                    <tr><td><code class="doc-inline-code">recurring_frequency</code>, <code class="doc-inline-code">recurring_interval</code>, <code class="doc-inline-code">days_of_week</code>, <code class="doc-inline-code">recurring_end_type</code>, <code class="doc-inline-code">recurring_end_value</code></td><td>Present only on a recurring event. <code class="doc-inline-code">days_of_week</code> reads <code class="doc-inline-code">"1111111"</code> for every frequency other than weekly and every_n_weeks.</td></tr>
                                    <tr><td><code class="doc-inline-code">rsvp_enabled</code>, <code class="doc-inline-code">rsvp_limit</code>, <code class="doc-inline-code">tickets_enabled</code></td><td>Registration and ticketing switches</td></tr>
                                    <tr><td><code class="doc-inline-code">seating_plan_id</code></td><td>Encoded ID of the event's seating plan, <code class="doc-inline-code">null</code> on a general-admission event</td></tr>
                                    <tr><td><code class="doc-inline-code">tickets</code></td><td>Present only while tickets are enabled. Each row has <code class="doc-inline-code">id</code>, <code class="doc-inline-code">type</code>, <code class="doc-inline-code">price</code>, <code class="doc-inline-code">quantity</code>, <code class="doc-inline-code">description</code>, <code class="doc-inline-code">sales_start_at</code>, <code class="doc-inline-code">sales_end_at</code>, <code class="doc-inline-code">volume_discount</code> and <code class="doc-inline-code">is_pass</code>. <code class="doc-inline-code">price</code> is a decimal string such as <code class="doc-inline-code">"25.000"</code>, here and on add-ons. A ticket that sells a seating band adds <code class="doc-inline-code">seating_band</code> and <code class="doc-inline-code">is_allocated</code>, and a pass adds its <code class="doc-inline-code">pass_*</code> settings.</td></tr>
                                    <tr><td><code class="doc-inline-code">addons</code></td><td>Present only while tickets are enabled. Each row has <code class="doc-inline-code">id</code>, <code class="doc-inline-code">type</code>, <code class="doc-inline-code">price</code>, <code class="doc-inline-code">quantity</code>, <code class="doc-inline-code">description</code>, <code class="doc-inline-code">image_url</code> and <code class="doc-inline-code">url</code>.</td></tr>
                                    <tr><td><code class="doc-inline-code">members</code></td><td>The event's performers, as an object rather than an array: each key is a talent schedule's encoded ID and each value holds <code class="doc-inline-code">name</code>, <code class="doc-inline-code">email</code> and <code class="doc-inline-code">youtube_url</code>. With no performers it reads <code class="doc-inline-code">[]</code>.</td></tr>
                                    <tr><td><code class="doc-inline-code">event_parts</code></td><td>Agenda parts, each with <code class="doc-inline-code">id</code>, <code class="doc-inline-code">name</code>, <code class="doc-inline-code">description</code>, <code class="doc-inline-code">start_time</code> and <code class="doc-inline-code">end_time</code></td></tr>
                                    <tr><td><code class="doc-inline-code">ticket_currency_code</code>, <code class="doc-inline-code">payment_method</code>, <code class="doc-inline-code">terms_url</code></td><td>Currency, payment method and the event's own terms link for tickets. <code class="doc-inline-code">terms_url</code> is read only.</td></tr>
                                    <tr><td><code class="doc-inline-code">schedules</code></td><td>Every schedule the event is on, each with <code class="doc-inline-code">id</code>, <code class="doc-inline-code">subdomain</code>, <code class="doc-inline-code">name</code> and <code class="doc-inline-code">type</code>, plus a <code class="doc-inline-code">group</code> object (<code class="doc-inline-code">id</code>, <code class="doc-inline-code">name</code>, <code class="doc-inline-code">slug</code>) where the event is filed under a sub-schedule there</td></tr>
                                    <tr><td><code class="doc-inline-code">created_at</code>, <code class="doc-inline-code">updated_at</code></td><td>ISO 8601 timestamps</td></tr>
                                </tbody>
                            </table>
                        </div>
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
            <span class="code-string">"is_draft"</span>: <span class="code-value">false</span>,
            <span class="code-string">"venue_id"</span>: <span class="code-string">"abc123"</span>,
            <span class="code-string">"schedule_type"</span>: <span class="code-string">"single"</span>,
            <span class="code-string">"tickets_enabled"</span>: <span class="code-value">true</span>,
            <span class="code-string">"tickets"</span>: [
                {
                    <span class="code-string">"id"</span>: <span class="code-string">"tkt1"</span>,
                    <span class="code-string">"type"</span>: <span class="code-string">"General"</span>,
                    <span class="code-string">"price"</span>: <span class="code-string">"25.000"</span>,
                    <span class="code-string">"quantity"</span>: <span class="code-value">100</span>,
                    <span class="code-string">"description"</span>: <span class="code-value">null</span>,
                    <span class="code-string">"sales_start_at"</span>: <span class="code-value">null</span>,
                    <span class="code-string">"sales_end_at"</span>: <span class="code-value">null</span>,
                    <span class="code-string">"volume_discount"</span>: <span class="code-value">null</span>,
                    <span class="code-string">"is_pass"</span>: <span class="code-value">false</span>
                }
            ],
            <span class="code-string">"members"</span>: {
                <span class="code-string">"tal789"</span>: {
                    <span class="code-string">"name"</span>: <span class="code-string">"The Quartet"</span>,
                    <span class="code-string">"email"</span>: <span class="code-string">"band@yourdomain.com"</span>,
                    <span class="code-string">"youtube_url"</span>: <span class="code-value">null</span>
                }
            },
            <span class="code-string">"event_parts"</span>: [
                {
                    <span class="code-string">"id"</span>: <span class="code-string">"prt1"</span>,
                    <span class="code-string">"name"</span>: <span class="code-string">"Opening Act"</span>,
                    <span class="code-string">"description"</span>: <span class="code-value">null</span>,
                    <span class="code-string">"start_time"</span>: <span class="code-string">"20:00"</span>,
                    <span class="code-string">"end_time"</span>: <span class="code-string">"20:45"</span>
                }
            ],
            <span class="code-string">"ticket_currency_code"</span>: <span class="code-string">"USD"</span>,
            <span class="code-string">"schedules"</span>: [
                {
                    <span class="code-string">"id"</span>: <span class="code-string">"abc123"</span>,
                    <span class="code-string">"subdomain"</span>: <span class="code-string">"my-venue"</span>,
                    <span class="code-string">"name"</span>: <span class="code-string">"My Venue"</span>,
                    <span class="code-string">"type"</span>: <span class="code-string">"venue"</span>
                }
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Create Event
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-post px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/events/{subdomain}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Create an event on the schedule identified by <code class="doc-inline-code">{subdomain}</code>. Requires owner or admin access on that schedule and a Pro or Enterprise plan. This endpoint carries its own throttle of 30 requests per minute in addition to the write bucket. On success it returns <code class="doc-inline-code">201</code> with <a href="#event-object" class="doc-link">the event object</a>. The schedule you post to becomes the event's owner, the one that sells its tickets and sees its sales, and no later request can change that.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Only the parameters listed here are read. Anything else at the top level of the body is dropped before the event is saved, so settings the API has no field for (sponsors, custom fields, promo codes, ticket options, seating) are made in the admin panel.</p>
                        <h3 class="doc-subheading">Core fields</h3>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">name</code></td><td>Yes</td><td>Event name (max 255 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">starts_at</code></td><td>Yes</td><td>Start date and time in <strong class="text-gray-900 dark:text-white">UTC</strong>, formatted <code class="doc-inline-code">Y-m-d H:i:s</code></td></tr>
                                    <tr><td><code class="doc-inline-code">duration</code></td><td>No</td><td>Length in hours, 0 to 8760. Decimals are allowed, so 1.5 is 90 minutes. There is no separate end-time field.</td></tr>
                                    <tr><td><code class="doc-inline-code">description</code></td><td>No</td><td>Full description, Markdown supported (max 10,000 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">short_description</code></td><td>No</td><td>Short description used in listings and previews (max 500 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">event_url</code></td><td>No</td><td>A single URL for an online event or an external event page (max 500 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">registration_url</code></td><td>No</td><td>External registration URL, used instead of on-platform tickets (max 2048 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">category_id</code></td><td>No</td><td>Category ID, which must be in this schedule's effective category list (see <a href="#list-categories" class="doc-link">List Categories</a>)</td></tr>
                                    <tr><td><code class="doc-inline-code">category</code></td><td>No</td><td>Category name, matched case- and punctuation-insensitively against the same list. Ignored when <code class="doc-inline-code">category_id</code> is present; an unmatched name returns <code class="doc-inline-code">422 Category not found</code>.</td></tr>
                                    <tr><td><code class="doc-inline-code">schedule</code></td><td>No</td><td>Sub-schedule slug to file the event under. An unknown slug returns <code class="doc-inline-code">422 Sub-schedule not found</code>.</td></tr>
                                    <tr><td><code class="doc-inline-code">external_id</code></td><td>No</td><td>The id your own system knows this event by (text or a number, max 255 characters). See <a href="#event-external-id" class="doc-link">External ID</a> below.</td></tr>
                                    <tr><td><code class="doc-inline-code">upsert</code></td><td>No</td><td><code class="doc-inline-code">true</code> to update the event that already has this <code class="doc-inline-code">external_id</code> instead of getting a <code class="doc-inline-code">409</code> (boolean). See <a href="#event-upsert" class="doc-link">Create or update in one call</a> below.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <h3 class="doc-subheading">Visibility</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Send no visibility flag at all and the event inherits the schedule's default for new events. Unlisted and Internal need an Enterprise plan <x-doc-badge plan="enterprise" />; on any lower plan they are stripped and the event is saved as a Draft so it never publishes by accident.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">The draft default is applied even to a partial request that omits <code class="doc-inline-code">is_draft</code>, so on a drafts-by-default schedule you must send <code class="doc-inline-code">is_draft: false</code> to publish straight away.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">is_draft</code></td><td>No</td><td>Draft: visible to your team in the admin panel, hidden from the public page (boolean)</td></tr>
                                    <tr><td><code class="doc-inline-code">is_private</code></td><td>No</td><td>Unlisted: kept off the calendar but reachable by direct link (boolean). Enterprise only.</td></tr>
                                    <tr><td><code class="doc-inline-code">is_internal</code></td><td>No</td><td>Internal: never public, and mutually exclusive with Unlisted (boolean). Enterprise only.</td></tr>
                                    <tr><td><code class="doc-inline-code">event_password</code></td><td>No</td><td>Password prompt on the event page. Only applies to an Unlisted event, and is discarded otherwise.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <h3 class="doc-subheading">Recurrence</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">The recurrence fields only take effect in a request that also carries <code class="doc-inline-code">schedule_type: "recurring"</code>. Without it a new event is a single one, whatever else is sent.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">schedule_type</code></td><td>No</td><td><code class="doc-inline-code">single</code> (default) or <code class="doc-inline-code">recurring</code></td></tr>
                                    <tr><td><code class="doc-inline-code">recurring_frequency</code></td><td>With recurring</td><td>daily, weekly, every_n_weeks, monthly_date, monthly_weekday, or yearly</td></tr>
                                    <tr><td><code class="doc-inline-code">days_of_week</code></td><td>With weekly</td><td>Seven characters of <code class="doc-inline-code">0</code> or <code class="doc-inline-code">1</code>, Sunday to Saturday. <code class="doc-inline-code">"0101010"</code> is Monday, Wednesday and Friday. Required for weekly and every_n_weeks.</td></tr>
                                    <tr><td><code class="doc-inline-code">recurring_interval</code></td><td>No</td><td>Week gap for every_n_weeks (integer, minimum 2)</td></tr>
                                    <tr><td><code class="doc-inline-code">recurring_end_type</code></td><td>No</td><td>never, on_date, or after_events</td></tr>
                                    <tr><td><code class="doc-inline-code">recurring_end_value</code></td><td>No</td><td>End date (Y-m-d) for on_date, or the number of occurrences for after_events</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <h3 class="doc-subheading">Tickets, RSVP and add-ons</h3>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">rsvp_enabled</code></td><td>No</td><td>Enable free registration, which collects a name and email without a payment step (boolean)</td></tr>
                                    <tr><td><code class="doc-inline-code">rsvp_limit</code></td><td>No</td><td>Cap on registrations per date (integer, minimum 1)</td></tr>
                                    <tr><td><code class="doc-inline-code">tickets_enabled</code></td><td>No</td><td>Enable ticketing (boolean)</td></tr>
                                    <tr><td><code class="doc-inline-code">ticket_currency_code</code></td><td>No</td><td>Three-letter ISO currency code, for example USD. Once the event has taken money, Update Event refuses to change it with a <code class="doc-inline-code">422</code>, because past sales and any later refund are denominated in it.</td></tr>
                                    <tr><td><code class="doc-inline-code">payment_method</code></td><td>No</td><td><code class="doc-inline-code">cash</code>, <code class="doc-inline-code">stripe</code>, <code class="doc-inline-code">paypal</code>, <code class="doc-inline-code">invoiceninja</code>, <code class="doc-inline-code">payment_url</code> or <code class="doc-inline-code">payfast</code>, with <code class="doc-inline-code">manual</code> accepted as an alias for <code class="doc-inline-code">cash</code>. See <a href="#event-payment-method" class="doc-link">Payment method</a> below.</td></tr>
                                    <tr><td><code class="doc-inline-code">payment_instructions</code></td><td>No</td><td>Instructions shown for manual payment (max 5000 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">tickets</code></td><td>No</td><td>Array of ticket types. Each takes <code class="doc-inline-code">type</code> (required, max 255 characters), <code class="doc-inline-code">quantity</code> (integer, 0 means unlimited), <code class="doc-inline-code">price</code> (0 or more), <code class="doc-inline-code">description</code> (max 1000 characters), <code class="doc-inline-code">sales_start_at</code> and <code class="doc-inline-code">sales_end_at</code> (UTC, <code class="doc-inline-code">Y-m-d H:i:s</code>).</td></tr>
                                    <tr><td><code class="doc-inline-code">addons</code></td><td>No</td><td>Array of paid extras sold alongside a ticket, such as parking or merchandise. Each takes <code class="doc-inline-code">type</code> (required, max 255 characters), <code class="doc-inline-code">quantity</code>, <code class="doc-inline-code">price</code>, <code class="doc-inline-code">description</code> (max 1000 characters) and <code class="doc-inline-code">url</code> (max 2000 characters). Only saved when <code class="doc-inline-code">tickets_enabled</code> is true.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Every row you send in <code class="doc-inline-code">tickets</code>, <code class="doc-inline-code">addons</code> or <code class="doc-inline-code">event_parts</code> is a new row on a new event. An <code class="doc-inline-code">id</code> copied from a read of another event is ignored. On <a href="#update-event-removes" class="doc-link">Update Event</a> the same <code class="doc-inline-code">id</code> is what addresses an existing row.</p>
                        <h3 class="doc-subheading" id="event-payment-method">Payment method</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">The API stores any method from the list without checking that it is connected on the account or can settle the event's currency, so check both yourself: <code class="doc-inline-code">payfast</code> only settles events priced in ZAR, and <code class="doc-inline-code">paypal</code> only the currencies listed under <a href="{{ route('marketing.docs.tickets') }}#paypal" class="doc-link">Connecting PayPal</a>, which is PayPal's own list minus the Hungarian forint, Japanese yen and New Taiwan dollar.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">On create, omitting the field uses the installation's <code class="doc-inline-code">DEFAULT_PAYMENT_METHOD</code> if one is set and is usable for the event's currency, falling back to <code class="doc-inline-code">cash</code>; send <code class="doc-inline-code">null</code> to mean cash explicitly. On update, omitting it leaves the stored value alone.</p>
                        <h3 class="doc-subheading">Agenda, venue and performers</h3>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">event_parts</code></td><td>No</td><td>Agenda segments within the event. Each takes <code class="doc-inline-code">name</code> (required, max 255 characters), <code class="doc-inline-code">description</code> (max 1000 characters), <code class="doc-inline-code">start_time</code> and <code class="doc-inline-code">end_time</code> (plain text, up to 10 characters).</td></tr>
                                    <tr><td><code class="doc-inline-code">venue_id</code></td><td>No</td><td>Encoded ID of an existing venue schedule. An ID that matches no schedule returns a <code class="doc-inline-code">404</code>.</td></tr>
                                    <tr><td><code class="doc-inline-code">venue_name</code></td><td>No</td><td>Venue name (max 255 characters). Send it together with <code class="doc-inline-code">venue_address1</code>.</td></tr>
                                    <tr><td><code class="doc-inline-code">venue_address1</code></td><td>No</td><td>Venue street address (max 255 characters). The pair is looked up, as an exact match on both, against venue schedules you own or follow; no match returns <code class="doc-inline-code">422 Venue not found</code> rather than creating one.</td></tr>
                                    <tr><td><code class="doc-inline-code">members</code></td><td>No</td><td>Performers, given as objects with <code class="doc-inline-code">name</code> and/or <code class="doc-inline-code">email</code>. Each is matched to an existing talent schedule you own or follow; no match returns <code class="doc-inline-code">422 Talent member not found</code>.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="doc-callout doc-callout-warning">
                            <div class="doc-callout-title">Send the venue name and address as a pair</div>
                            <p>The lookup only runs when both are present. With just one of them the request skips it and falls through to the event form's own rule, which attaches the best-matching venue anywhere on the install or creates a new, unclaimed venue schedule from what you sent.</p>
                        </div>
                        <h3 class="doc-subheading">What the schedule's type decides</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">The schedule you post to is always on the event, in the place its type gives it:</p>
                        <ul class="doc-list mb-6">
                            <li><strong class="text-gray-900 dark:text-white">Venue.</strong> It is the event's venue. <code class="doc-inline-code">venue_id</code> and the name and address pair are overridden.</li>
                            <li><strong class="text-gray-900 dark:text-white">Talent.</strong> It is the event's one performer. <code class="doc-inline-code">members</code> is overridden, so other performers cannot be added through a talent schedule.</li>
                            <li><strong class="text-gray-900 dark:text-white">Curator.</strong> The event is listed on it, and takes its venue and performers from the request.</li>
                        </ul>
                        <h3 class="doc-subheading" id="event-external-id">External ID</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6"><code class="doc-inline-code">external_id</code> is the id your own system knows an event by: a post number, a row's key, a UUID. Send it when you create an event, and your next sync can find that event again instead of adding a second one. It is returned in <a href="#event-object" class="doc-link">the event object</a> and in every event webhook, and <code class="doc-inline-code">GET /api/events?external_id=...</code> looks an event up by it.</p>
                        <ul class="doc-list mb-6">
                            <li>It is unique for the schedule that owns the event, so two schedules fed by two systems may both have an event numbered 1. An event that has none reads <code class="doc-inline-code">null</code>.</li>
                            <li>It is compared exactly: <code class="doc-inline-code">aB3</code> and <code class="doc-inline-code">Ab3</code> are two ids. A number is stored as its digits, and spaces around the value are dropped.</li>
                            <li>Creating an event with an id the schedule already has an event for returns <code class="doc-inline-code">409</code>, with that event's <code class="doc-inline-code">id</code> under <code class="doc-inline-code">data</code>, and nothing is created, unless the request asks to <a href="#event-upsert" class="doc-link">update it instead</a>. Two requests that carry the same new id at the same moment end the same way: one creates the event, the other gets the <code class="doc-inline-code">409</code>.</li>
                            <li><a href="#update-event" class="doc-link">Update Event</a> sets it on an event that has none, changes it, or clears it with <code class="doc-inline-code">null</code>. An update that does not mention it leaves it alone. Only an owner or admin of the schedule that owns the event can change it, and an id another event of that schedule holds is refused with <code class="doc-inline-code">422</code>.</li>
                            <li>The admin panel neither shows nor changes it, and editing an event there keeps it.</li>
                        </ul>
                        <h3 class="doc-subheading" id="event-upsert">Create or update in one call</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">A sync usually does not know whether it has sent a record before. Send <code class="doc-inline-code">"upsert": true</code> with an <code class="doc-inline-code">external_id</code> and this endpoint decides: the first call creates the event and returns <code class="doc-inline-code">201</code>, and every later call with the same id updates that event and returns <code class="doc-inline-code">200</code>. <code class="doc-inline-code">meta.created</code> says which happened.</p>
                        <ul class="doc-list mb-6">
                            <li>The update follows the rules of <a href="#update-event" class="doc-link">Update Event</a>, not the rules above. Nothing is required, a field the request does not carry keeps its stored value, and none of a new event's defaults are applied again: an event you turned into a draft in the admin panel stays a draft when the source sends it again.</li>
                            <li>That includes the lists. Send <code class="doc-inline-code">tickets</code>, <code class="doc-inline-code">addons</code> or <code class="doc-inline-code">event_parts</code> only when you mean to change them, and send each existing row with its <code class="doc-inline-code">id</code>: a row without one is a new row, and a stored row the list leaves out is retired, as <a href="#update-event-removes" class="doc-link">an update does</a>.</li>
                            <li>The update is not counted against the <a href="#event-daily-cap" class="doc-link">daily cap</a>.</li>
                            <li><code class="doc-inline-code">upsert</code> without an <code class="doc-inline-code">external_id</code> is refused with <code class="doc-inline-code">422</code>, because there is nothing to match on.</li>
                            <li>The event is looked up among the events this schedule owns. An event another schedule owns is never matched, even when this schedule lists it.</li>
                        </ul>
                        <div class="doc-code-block mb-6">
                            <div class="doc-code-header"><span>Create or update</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/events/your-schedule"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{"external_id": "post-4686933", "upsert": true, "name": "Spring concert", "starts_at": "2026-05-09 17:30:00"}'</span></code></pre>
                        </div>
                        <h3 class="doc-subheading" id="event-daily-cap">Daily cap</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">The hosted service caps how many events one schedule, and one account across its schedules, may create in a day, as an anti-abuse measure. The shipped defaults are 500 a day per schedule on Pro, 1,000 on Enterprise and 100 during a trial, and three times that per account. A bulk import that trips the cap gets a <code class="doc-inline-code">422</code> whose body carries <code class="doc-inline-code">"code": "event_create_limit"</code> beside <code class="doc-inline-code">error</code>, so it can stop at the first refusal and resume the next day. Selfhosted installs have no cap.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/events/my-venue"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -H <span class="code-string">"Accept: application/json"</span> \
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
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (201)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"id"</span>: <span class="code-string">"evt123"</span>,
            <span class="code-string">"name"</span>: <span class="code-string">"Jazz Night"</span>,
            ...
        },
        <span class="code-string">"meta"</span>: {
            <span class="code-string">"message"</span>: <span class="code-string">"Event created successfully"</span>
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Update Event -->
            <section id="update-event" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Update Event
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-put px-2 py-1 rounded text-sm font-medium">PUT</span>
                            <code class="doc-inline-code">/api/events/{id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Update an event by its encoded ID. Takes the same parameters as <a href="#create-event" class="doc-link">Create Event</a>, and supports partial updates: send only the fields you want to change. Requires owner or admin access on one of the event's schedules and a Pro or Enterprise plan. Returns <code class="doc-inline-code">200</code> with <a href="#event-object" class="doc-link">the event object</a>.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">The update runs through one of the event's schedules that you own or administer. If your account created the event but is neither owner nor admin of any of its schedules, the reply is <code class="doc-inline-code">422 No schedule found for this event that you have access to</code>.</p>
                        <h3 class="doc-subheading" id="update-event-kept">What an update leaves alone</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">A field the request does not carry keeps its stored value. That covers the start time, the visibility flags, the recurrence, the payment method, the lists of ticket types, add-ons and agenda parts, the venue, the performers and the other schedules the event is listed on. The schedule that owns the event never changes, and the event's <a href="#event-external-id" class="doc-link"><code class="doc-inline-code">external_id</code></a> stays unless the request names it.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">What the API has no field for stays as the admin panel left it: promo codes, a ticket type's <strong class="text-gray-900 dark:text-white">Max Per Order</strong>, custom fields and pass settings, the dates added to or skipped from a series, sponsors and the event's custom fields. An update that only renames an event changes its name.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">A venue, performer or listing the request does not name also keeps its answer. One that had not yet accepted the event, or had turned it down, is still in that state afterwards: an update does not accept an event on a schedule's behalf. <code class="doc-inline-code">venue_id</code>, <code class="doc-inline-code">venue_name</code> and <code class="doc-inline-code">venue_address1</code> sent as <code class="doc-inline-code">null</code> name no venue, so the event stays where it is.</p>
                        <ul class="doc-list mb-6">
                            <li>To change the recurrence, send <code class="doc-inline-code">schedule_type</code> with the full set of recurrence fields, since one left out goes back to its default. Without <code class="doc-inline-code">schedule_type</code> the stored frequency, interval and end rule are kept and those fields in the request are ignored; only <code class="doc-inline-code">days_of_week</code> is still applied. <code class="doc-inline-code">schedule_type: "single"</code> turns a recurring event into a single one.</li>
                            <li><code class="doc-inline-code">schedule</code> moves the event to another sub-schedule. There is no way to take an event out of a sub-schedule through the API.</li>
                            <li>A visibility flag takes effect as on create: Unlisted and Internal need Enterprise, and <code class="doc-inline-code">is_internal: true</code> also makes the event a Draft.</li>
                        </ul>
                        <h3 class="doc-subheading" id="update-event-removes">What an update replaces or removes</h3>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Request</th><th>What it does</th></tr></thead>
                                <tbody>
                                    <tr><td>Carries <code class="doc-inline-code">tickets</code>, <code class="doc-inline-code">addons</code> or <code class="doc-inline-code">event_parts</code></td><td>Replaces that list, row by row. A row with the <code class="doc-inline-code">id</code> a read returned updates that row and keeps whatever the row does not send, so a ticket type keeps its ID and what was sold against it. A row without an <code class="doc-inline-code">id</code> is added. A stored row the list leaves out is retired (a ticket type or an add-on) or deleted (an agenda part). A ticket type's <code class="doc-inline-code">type</code> may be <code class="doc-inline-code">null</code> here, which is what a read returns for an event with one unnamed ticket type. An <code class="doc-inline-code">id</code> that is not one of this event's rows is refused with a <code class="doc-inline-code">422</code> naming the row, and nothing is saved.</td></tr>
                                    <tr><td>Carries <code class="doc-inline-code">tickets_enabled: false</code></td><td>Retires every ticket type and add-on on the event, without asking, even when people have bought them.</td></tr>
                                    <tr><td>Carries <code class="doc-inline-code">schedule_type: "single"</code></td><td>Turns a recurring event into a single one, and with that clears the dates added to and skipped from the series. Refused with a <code class="doc-inline-code">422</code> while the event sells a pass counted per date (<code class="doc-inline-code">per_occurrence</code>), which only a recurring event can have: give the pass another usage type first.</td></tr>
                                    <tr><td>Carries <code class="doc-inline-code">venue_id</code>, or <code class="doc-inline-code">venue_name</code> with <code class="doc-inline-code">venue_address1</code></td><td>Moves the event to that venue, off the one it was at. There is no way to leave an event with no venue through the API.</td></tr>
                                    <tr><td>Carries <code class="doc-inline-code">members</code></td><td>Replaces the performers. One you could pick in the event form (a schedule you own, administer or view, or follow while it accepts requests) and leave out of the list is taken off the event. When the update runs through a talent schedule the list is set aside, as on create, and the performers stay as they are.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Some schedules always stay on the event: the one the update runs through, the one that created it, and any that sit outside your account. The schedules an event is listed on cannot be named in a request, so the API neither adds nor removes one.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Delete Event
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-delete px-2 py-1 rounded text-sm font-medium">DELETE</span>
                            <code class="doc-inline-code">/api/events/{id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Permanently delete an event. Requires owner or admin access on one of its schedules and a Pro or Enterprise plan. There is no undo, so if you may want it back, hide the event with <code class="doc-inline-code">is_draft</code> or <a href="#cancel-event" class="doc-link">cancel it</a> instead.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">An event that has sales is not deleted. The reply is <code class="doc-inline-code">422</code> and nothing changes, whether those sales are paid, refunded or cancelled, because the sale records would go with the event. <a href="#cancel-event" class="doc-link">Cancel it</a> instead, which keeps the records and lets you tell the buyers.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Deleting also removes the synced copy from any connected Google Calendar, Outlook calendar and CalDAV calendar, cancels any running boost campaign, and deletes its gallery photos and sponsor logo files. Unless the event was a draft, an <code class="doc-inline-code">event.deleted</code> <a href="{{ route('marketing.docs.developer.webhooks') }}" class="doc-link">webhook</a> is sent with the event's final state.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">An appointment booking is the exception. It is cancelled, not deleted: the slot is freed, the booking and its sale are kept, the guest of a booking that was still live is emailed, and no webhook is sent. That reply is a bare <code class="doc-inline-code">{"message": "Appointment cancelled"}</code> with no <code class="doc-inline-code">data</code> wrapper.</p>
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
    
            <!-- Cancel Event -->
            <section id="cancel-event" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Cancel Event
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-post px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/events/{id}/cancel</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Call an event off without deleting it. The event keeps its page, which now says it is cancelled, and it keeps every sale and registration. It leaves the schedule's public calendar, stops selling, and is removed from any connected Google Calendar, Outlook calendar and CalDAV calendar. Any running boost campaign is stopped and refunded, and installment plans stop charging. Requires owner or admin access on one of its schedules and a Pro or Enterprise plan.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">This is the way to stop an event that has sales, which <a href="#delete-event" class="doc-link">Delete Event</a> refuses. Sales are not refunded by this call: refund each one through <a href="#update-sale" class="doc-link">Update Sale</a> or in the admin panel.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Send <code class="doc-inline-code">notify_attendees</code> as <code class="doc-inline-code">true</code> to email the people who bought, registered or asked to be told, with an optional <code class="doc-inline-code">message</code> of up to 280 characters under it. Buyers are emailed only when the schedule sends from its own address. Nobody is emailed about a draft, and nobody is emailed unless you ask.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Unless the event is a draft, an <code class="doc-inline-code">event.cancelled</code> <a href="{{ route('marketing.docs.developer.webhooks') }}" class="doc-link">webhook</a> is sent. Cancelling an event that is already cancelled changes nothing and answers <code class="doc-inline-code">200</code>. An appointment booking is cancelled through its sale, which frees the slot and emails the guest. The reply is the event object.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/events/evt123/cancel"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{"notify_attendees": true, "message": "The hall is flooded."}'</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"id"</span>: <span class="code-string">"evt123"</span>,
            <span class="code-string">"name"</span>: <span class="code-string">"Jazz Night"</span>,
            <span class="code-string">"is_cancelled"</span>: <span class="code-value">true</span>,
            <span class="code-string">"cancelled_at"</span>: <span class="code-string">"2026-10-08T09:30:00+00:00"</span>
        },
        <span class="code-string">"meta"</span>: {
            <span class="code-string">"message"</span>: <span class="code-string">"Event cancelled successfully"</span>
        }
    }</code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Restore Event -->
            <section id="restore-event" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                            </svg>
                            Restore Event
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-post px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/events/{id}/restore</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Undo a cancellation. The event returns to the public calendar and to connected calendars, and sales reopen. Requires owner or admin access on one of its schedules and a Pro or Enterprise plan.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Nobody is emailed. A boost campaign or an installment plan that the cancellation stopped stays stopped. Unless the event is a draft, an <code class="doc-inline-code">event.updated</code> webhook is sent, with <code class="doc-inline-code">is_cancelled</code> back to <code class="doc-inline-code">false</code>.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Restoring an event that is not cancelled changes nothing and answers <code class="doc-inline-code">200</code>. An appointment booking cannot be restored and answers <code class="doc-inline-code">422</code>: its slot was freed when it was cancelled.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/events/evt123/restore"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Response (200)</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code>{
        <span class="code-string">"data"</span>: {
            <span class="code-string">"id"</span>: <span class="code-string">"evt123"</span>,
            <span class="code-string">"name"</span>: <span class="code-string">"Jazz Night"</span>,
            <span class="code-string">"is_cancelled"</span>: <span class="code-value">false</span>,
            <span class="code-string">"cancelled_at"</span>: <span class="code-value">null</span>
        },
        <span class="code-string">"meta"</span>: {
            <span class="code-string">"message"</span>: <span class="code-string">"Event restored successfully"</span>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            Upload Flyer
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-post px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/events/flyer/{event_id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Set the flyer image for an event. Send it as <code class="doc-inline-code">multipart/form-data</code> in a <code class="doc-inline-code">flyer_image</code> field, not as JSON. Requires owner or admin access on one of the event's schedules and a Pro or Enterprise plan.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Constraint</th><th>Value</th></tr></thead>
                                <tbody>
                                    <tr><td>Formats</td><td>jpg, jpeg, png, gif, webp</td></tr>
                                    <tr><td>Maximum size</td><td>10 MB</td></tr>
                                    <tr><td>Existing flyer</td><td>Replaced, and the old file is deleted from storage</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mt-6">The response is the full event record, so you can read the new <code class="doc-inline-code">flyer_image_url</code> straight back from <code class="doc-inline-code">data</code>. There is no endpoint for removing a flyer.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            List Categories
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-get px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/categories</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns the built-in event categories with their integer IDs and English names. Pass an <code class="doc-inline-code">id</code> as <code class="doc-inline-code">category_id</code> when creating or updating an event. The list is not paginated.</p>
                        <h3 class="doc-subheading">Categories for one schedule</h3>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-get px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/categories/{subdomain}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">A schedule can rename, remove or add categories of its own, and <code class="doc-inline-code">category_id</code> is validated against that effective list rather than the global one. Call this variant to get the exact set a given schedule will accept, and use it whenever the schedule has customised its categories. It returns the same <code class="doc-inline-code">id</code> and <code class="doc-inline-code">name</code> pairs, and <code class="doc-inline-code">404</code> for a subdomain that does not exist.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            List Sales
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-get px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/sales</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of sales, newest order first, each as <a href="#sale-object" class="doc-link">the sale object</a>. Deleted sales are excluded, and on the hosted service so are sales on schedules that are not Pro or Enterprise. RSVP registrations appear here too, as zero-value paid sales.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">The list covers events your account created, events one of your schedules created, and events another schedule created that your venue or talent schedule has accepted. A curator schedule that only lists somebody else's event does not open that event's sales, here or on the other sale endpoints.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Show Sale
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-get px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/sales/{id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a single sale by its encoded ID. Requires owner or admin access on the event's schedule and a Pro or Enterprise plan. A sale that exists but is not yours to read returns <code class="doc-inline-code">403</code>; a deleted or unknown one returns <code class="doc-inline-code">404</code>.</p>
                        <h3 class="doc-subheading" id="sale-object">The sale object</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Every sale endpoint except Delete Sale returns this shape, and so do the <code class="doc-inline-code">sale.*</code> <a href="{{ route('marketing.docs.developer.webhooks') }}#payload" class="doc-link">webhooks</a>. There is no currency field: read the event's <code class="doc-inline-code">ticket_currency_code</code>.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Field</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">id</code>, <code class="doc-inline-code">event_id</code>, <code class="doc-inline-code">event_name</code></td><td>Encoded sale ID, and the event the sale is on</td></tr>
                                    <tr><td><code class="doc-inline-code">subdomain</code></td><td>The schedule the sale was made through</td></tr>
                                    <tr><td><code class="doc-inline-code">name</code>, <code class="doc-inline-code">email</code>, <code class="doc-inline-code">phone</code></td><td>The buyer, or the named guest on a guest's row</td></tr>
                                    <tr><td><code class="doc-inline-code">event_date</code></td><td>The date of the event the sale is for (<code class="doc-inline-code">Y-m-d</code>)</td></tr>
                                    <tr><td><code class="doc-inline-code">status</code></td><td><code class="doc-inline-code">unpaid</code>, <code class="doc-inline-code">paid</code>, <code class="doc-inline-code">cancelled</code>, <code class="doc-inline-code">refunded</code> or <code class="doc-inline-code">expired</code>. A sale whose payment arrived for a different amount reads <code class="doc-inline-code">amount_mismatch</code> while it is held for review.</td></tr>
                                    <tr><td><code class="doc-inline-code">payment_method</code></td><td>The event's payment method when the sale was made, <code class="doc-inline-code">rsvp</code> for a free registration, or <code class="doc-inline-code">import</code> for an imported attendee</td></tr>
                                    <tr><td><code class="doc-inline-code">payment_amount</code></td><td>What the buyer agreed to pay, as a number</td></tr>
                                    <tr><td><code class="doc-inline-code">volume_discount_amount</code>, <code class="doc-inline-code">discount_amount</code>, <code class="doc-inline-code">gift_card_amount</code></td><td>What a volume discount, a promo code and a gift card took off. <code class="doc-inline-code">null</code> when there was none.</td></tr>
                                    <tr><td><code class="doc-inline-code">transaction_reference</code></td><td>The payment provider's reference, or the note recorded when the sale was marked paid by hand</td></tr>
                                    <tr><td><code class="doc-inline-code">secret</code></td><td>The token that opens the buyer's ticket page and QR code. Present only when the key belongs to the account that created the event or placed the sale.</td></tr>
                                    <tr><td><code class="doc-inline-code">tickets</code></td><td>A row per ticket type and add-on, each with <code class="doc-inline-code">ticket_id</code>, <code class="doc-inline-code">type</code>, <code class="doc-inline-code">quantity</code>, <code class="doc-inline-code">price</code>, <code class="doc-inline-code">is_addon</code> and <code class="doc-inline-code">is_pass</code>. A pass adds <code class="doc-inline-code">pass_usage_type</code>, <code class="doc-inline-code">pass_visits_used</code>, <code class="doc-inline-code">pass_max_uses</code> and <code class="doc-inline-code">pass_expires_at</code>. On an event with allocated seating a row also carries <code class="doc-inline-code">seats</code>, the labels of the seats it holds.</td></tr>
                                    <tr><td><code class="doc-inline-code">total_quantity</code></td><td>How many tickets this row holds</td></tr>
                                    <tr><td><code class="doc-inline-code">group_id</code>, <code class="doc-inline-code">is_primary</code></td><td>Set on an order bought for several named guests, see below. An ordinary sale has <code class="doc-inline-code">group_id: null</code> and <code class="doc-inline-code">is_primary: false</code>.</td></tr>
                                    <tr><td><code class="doc-inline-code">order_id</code>, <code class="doc-inline-code">is_order_primary</code></td><td>Set on a purchase that covered several events, see below</td></tr>
                                    <tr><td><code class="doc-inline-code">created_at</code>, <code class="doc-inline-code">updated_at</code></td><td>ISO 8601 timestamps</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <h3 class="doc-subheading" id="sale-groups-and-orders">Guests, orders and amounts</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">An order bought for several named guests is stored as one row per guest, all sharing a <code class="doc-inline-code">group_id</code>. The row with <code class="doc-inline-code">is_primary</code> set to true holds the totals for the whole order; the other rows report zero so you do not double-count when you add them up. Every row in a group belongs to the same event.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">A purchase that covered several events shares an <code class="doc-inline-code">order_id</code> instead, one row per event, with <code class="doc-inline-code">is_order_primary</code> on the anchoring row. The two nest: a leg of an order can itself be split across named guests, so a row may carry both.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6"><code class="doc-inline-code">payment_amount</code> is what the buyer agreed to pay, not what has been collected. The two differ for a sale bought on an <a href="{{ route('marketing.docs.tickets') }}#installments" class="doc-link">installment plan</a>: the sale reads <code class="doc-inline-code">paid</code> with the full total from the first payment onwards, because the ticket is issued then, while the rest arrives over the following months. Reconcile against Stripe rather than against this field if you are counting money in the bank.</p>
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
            <span class="code-string">"event_date"</span>: <span class="code-string">"2025-03-15"</span>,
            <span class="code-string">"status"</span>: <span class="code-string">"paid"</span>,
            <span class="code-string">"payment_method"</span>: <span class="code-string">"stripe"</span>,
            <span class="code-string">"payment_amount"</span>: <span class="code-value">50</span>,
            <span class="code-string">"order_id"</span>: <span class="code-value">null</span>,
            <span class="code-string">"is_order_primary"</span>: <span class="code-value">false</span>,
            <span class="code-string">"tickets"</span>: [
                {
                    <span class="code-string">"ticket_id"</span>: <span class="code-string">"tkt1"</span>,
                    <span class="code-string">"quantity"</span>: <span class="code-value">2</span>,
                    <span class="code-string">"price"</span>: <span class="code-value">25</span>,
                    <span class="code-string">"type"</span>: <span class="code-string">"General"</span>,
                    <span class="code-string">"is_addon"</span>: <span class="code-value">false</span>,
                    <span class="code-string">"is_pass"</span>: <span class="code-value">false</span>
                }
            ],
            <span class="code-string">"total_quantity"</span>: <span class="code-value">2</span>,
            <span class="code-string">"group_id"</span>: <span class="code-value">null</span>,
            <span class="code-string">"is_primary"</span>: <span class="code-value">false</span>,
            ...
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Create Sale
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-post px-2 py-1 rounded text-sm font-medium">POST</span>
                            <code class="doc-inline-code">/api/sales</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Record a sale against an event, for example when someone paid you at the door or through a channel Event Schedule does not handle. The event must have ticketing enabled and still be selling, and each ticket is checked individually, exactly as guest checkout does: a priced row needs the schedule to be on Pro or Enterprise, while a zero-price row sells on any plan. Until the schedule is on a paid plan, though, the sale does not appear in <a href="#list-sales" class="doc-link">List Sales</a>, and <a href="#show-sale" class="doc-link">Show Sale</a> and <a href="#update-sale" class="doc-link">Update Sale Status</a> return <code class="doc-inline-code">403</code> for it, so mark it paid from the Sales page instead.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">event_id</code></td><td>Yes</td><td>Encoded event ID</td></tr>
                                    <tr><td><code class="doc-inline-code">name</code></td><td>Yes</td><td>Buyer name (max 255 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">email</code></td><td>Yes</td><td>Buyer email (max 255 characters)</td></tr>
                                    <tr><td><code class="doc-inline-code">tickets</code></td><td>Yes</td><td>Object mapping ticket identifiers to quantities, each 1 or more. A key may be an encoded ticket ID or a ticket type name.</td></tr>
                                    <tr><td><code class="doc-inline-code">addons</code></td><td>No</td><td>Object mapping encoded add-on IDs to quantities</td></tr>
                                    <tr><td><code class="doc-inline-code">event_date</code></td><td>No</td><td>Which date of the event the sale is for (Y-m-d). Defaults to the event's start date in the schedule's timezone, and is <strong class="text-gray-900 dark:text-white">required</strong> for a recurring event.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <h3 class="doc-subheading">What happens on success</h3>
                        <ul class="doc-list mb-6">
                            <li>The reply is <code class="doc-inline-code">201</code> with <a href="#sale-object" class="doc-link">the sale object</a>, <code class="doc-inline-code">secret</code> included, since your key placed the sale.</li>
                            <li>The sale is created as <code class="doc-inline-code">unpaid</code>. You cannot set the status from the request; use <a href="#update-sale" class="doc-link">Update Sale Status</a> once you have the money.</li>
                            <li>A sale whose total comes to zero is marked <code class="doc-inline-code">paid</code> immediately.</li>
                            <li>Any volume discount configured on the ticket type is applied to the total.</li>
                            <li>On an event with <a href="{{ route('marketing.docs.allocated_seating') }}" class="doc-link">allocated seating</a>, the best available seats are assigned, since this path has no seat picker.</li>
                            <li>A <code class="doc-inline-code">sale.created</code> <a href="{{ route('marketing.docs.developer.webhooks') }}" class="doc-link">webhook</a> fires, plus <code class="doc-inline-code">sale.paid</code> for a zero-total sale.</li>
                        </ul>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Inventory is checked under a lock, so you cannot oversell through this endpoint. Common <code class="doc-inline-code">422</code> replies are a past event or occurrence, a ticket whose sales window has not opened or has closed, and a quantity larger than the remaining stock, which reports how many are left.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Update Sale Status
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-put px-2 py-1 rounded text-sm font-medium">PUT</span>
                            <code class="doc-inline-code">/api/sales/{id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Move a sale to a new status by sending an <code class="doc-inline-code">action</code>. Requires owner or admin access on the event's schedule and a Pro or Enterprise plan. Which actions are available depends on where the sale is now; an action the current status does not allow returns a <code class="doc-inline-code">422</code> naming both. <code class="doc-inline-code">refund</code> asks one thing more, because the money leaves the event creator's account: the schedule you act through must have created the event or accepted its place on it, or the reply is <code class="doc-inline-code">403</code>. A change that goes through returns <code class="doc-inline-code">200</code> with <a href="#sale-object" class="doc-link">the sale object</a> in its new state.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Required</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">action</code></td><td>Yes</td><td><code class="doc-inline-code">mark_paid</code>, <code class="doc-inline-code">refund</code> or <code class="doc-inline-code">cancel</code></td></tr>
                                    <tr><td><code class="doc-inline-code">amount</code></td><td>No</td><td>With <code class="doc-inline-code">refund</code>: the part to send back, 0.01 or more. See <a href="#refunds" class="doc-link">Refunds</a>.</td></tr>
                                    <tr><td><code class="doc-inline-code">idempotency_key</code></td><td>No</td><td>With <code class="doc-inline-code">refund</code>: a key of your own that makes a retry safe</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Action</th><th>From Status</th><th>To Status</th><th>Webhook</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">mark_paid</code></td><td>unpaid</td><td>paid</td><td><code class="doc-inline-code">sale.paid</code></td></tr>
                                    <tr><td><code class="doc-inline-code">refund</code></td><td>paid</td><td>refunded</td><td><code class="doc-inline-code">sale.refunded</code></td></tr>
                                    <tr><td><code class="doc-inline-code">refund</code> with <code class="doc-inline-code">amount</code></td><td>paid</td><td>paid</td><td>none</td></tr>
                                    <tr><td><code class="doc-inline-code">cancel</code></td><td>unpaid, paid</td><td>cancelled</td><td><code class="doc-inline-code">sale.cancelled</code></td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Cancelling or fully refunding releases the seats back into stock and notifies anyone on the waitlist for that date; a partial refund does neither. A named guest's row inside a <code class="doc-inline-code">group_id</code> returns <code class="doc-inline-code">403</code>: act on the group's primary row (<code class="doc-inline-code">is_primary</code>), which carries the change to every guest. On a multi-event order the row with <code class="doc-inline-code">is_order_primary</code> carries it to every leg, while acting on any other leg changes only that leg and its own guests.</p>
                        <h3 class="doc-subheading" id="refunds">Refunds</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">For a Stripe or PayPal sale, <code class="doc-inline-code">refund</code> sends the money back through the provider and then updates the status. Send an optional <code class="doc-inline-code">amount</code> to return part of it; omit it and the whole remaining balance goes back. A partial refund leaves the sale <code class="doc-inline-code">paid</code>, fires no webhook, and returns <code class="doc-inline-code">200</code> with the message <code class="doc-inline-code">Partial refund sent</code>.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Every other method - Invoice Ninja, Payfast, a payment link, cash, or a sale marked paid by hand - only records the refund and backs the amount out of your revenue figures, and it ignores <code class="doc-inline-code">amount</code>: the whole sale is recorded as refunded. Issue the money in your payment provider, then call this to keep the two in step.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">It does not work the other way round. A refund issued from the Stripe or PayPal dashboard is not reported back to Event Schedule, so the sale stays <code class="doc-inline-code">paid</code> and its tickets keep scanning. Refund through this endpoint or the Sales page instead.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">A refund the gateway refuses returns <code class="doc-inline-code">422</code> and leaves the sale <code class="doc-inline-code">paid</code>. A refund whose outcome could not be confirmed returns <code class="doc-inline-code">409</code>: nothing is retried automatically, because retrying a refund that may already have gone through is how one refund becomes two. Check it against your provider before acting.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Send an <code class="doc-inline-code">idempotency_key</code> of your own, up to 64 letters, digits, <code class="doc-inline-code">_</code>, <code class="doc-inline-code">.</code>, <code class="doc-inline-code">:</code> or <code class="doc-inline-code">-</code>, to make retrying safe. A repeat carrying the same key returns the first attempt's outcome instead of issuing a second refund, and a repeat sent while the first is still running returns <code class="doc-inline-code">409</code>. Without a key, a retried request is a second refund.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">A payment plan is refunded in full only: sending <code class="doc-inline-code">amount</code> for one returns <code class="doc-inline-code">422</code>. Each collected payment goes back separately, and an attempt that stops partway can be repeated to return the rest.</p>
                    </div>
                    <div class="api-endpoint-code">
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>cURL</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X PUT <span class="code-string">"{{ config('app.url') }}/api/sales/sale123"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{"action": "mark_paid"}'</span></code></pre>
                        </div>
                        <div class="doc-code-block">
                            <div class="doc-code-header"><span>Partial refund</span><button class="doc-copy-btn">Copy</button></div>
                            <pre><code><span class="code-keyword">curl</span> -X PUT <span class="code-string">"{{ config('app.url') }}/api/sales/sale123"</span> \
         -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
         -H <span class="code-string">"Content-Type: application/json"</span> \
         -d <span class="code-string">'{"action": "refund", "amount": 10, "idempotency_key": "sale123-refund-1"}'</span></code></pre>
                        </div>
                    </div>
                </div>
            </section>
    
            <!-- Delete Sale -->
            <section id="delete-sale" class="doc-section api-endpoint-section">
                <div class="api-endpoint-row">
                    <div class="api-endpoint-desc">
                        <h2 class="doc-heading">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Delete Sale
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-delete px-2 py-1 rounded text-sm font-medium">DELETE</span>
                            <code class="doc-inline-code">/api/sales/{id}</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Remove a sale from your records. It is cancelled first, so its seats return to stock, and then flagged as deleted: it stops appearing in <a href="#list-sales" class="doc-link">List Sales</a> and in the admin panel, and <a href="#show-sale" class="doc-link">Show Sale</a> returns <code class="doc-inline-code">404</code> for it. Requires owner or admin access on the event's schedule and a Pro or Enterprise plan.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Deleting the row with <code class="doc-inline-code">is_order_primary</code> deletes the whole multi-event order, and deleting a group's primary row deletes its guests with it. A named guest's row returns <code class="doc-inline-code">403</code>.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Deleting moves no money and sends no webhook, not even <code class="doc-inline-code">sale.cancelled</code>. To give a buyer their money back, <a href="#refunds" class="doc-link">refund</a> the sale first.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                            List Feedback
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-get px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/feedback</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of post-event feedback, meaning the star ratings and comments attendees leave after an event, for schedules you own or administer. Newest first. Read only: there is no endpoint for creating or deleting feedback.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Only feedback attached to a paid, undeleted sale is returned, which is the same rule the guest page applies, so a cancelled or refunded order's rating never shows up here. On the hosted service the schedule must be on a Pro or Enterprise plan, since collecting feedback is itself a Pro feature.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td><code class="doc-inline-code">event_id</code></td><td>Filter by event (encoded event ID)</td></tr>
                                    <tr><td><code class="doc-inline-code">subdomain</code></td><td>Filter by schedule subdomain</td></tr>
                                    <tr><td><code class="doc-inline-code">event_date</code></td><td>Filter by the date of the event attended (Y-m-d)</td></tr>
                                    <tr><td><code class="doc-inline-code">min_rating</code></td><td>Only return ratings of at least this value (1-5)</td></tr>
                                    <tr><td><code class="doc-inline-code">from</code></td><td>Only feedback submitted on or after this date (Y-m-d)</td></tr>
                                    <tr><td><code class="doc-inline-code">to</code></td><td>Only feedback submitted on or before this date (Y-m-d)</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="doc-callout doc-callout-warning">
                            <div class="doc-callout-title">Rows carry attendee contact details</div>
                            <p>Each record includes <code class="doc-inline-code">attendee_name</code> and <code class="doc-inline-code">attendee_email</code>. Strip both before you render feedback on a public page.</p>
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                            </svg>
                            List Fan Content
                        </h2>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="api-method-pill api-method-pill-get px-2 py-1 rounded text-sm font-medium">GET</span>
                            <code class="doc-inline-code">/api/fan-content</code>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns fan comments, photos and videos submitted on events for schedules you own or administer, all three kinds merged into one feed, newest first. Approved items only by default, which is what you want when displaying them on an external site. Submitter email addresses are never included. Read only: approve and reject submissions in the admin panel.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Each kind of submission has its own <code class="doc-inline-code">id</code> sequence, so an <code class="doc-inline-code">id</code> is only unique within a <code class="doc-inline-code">type</code>. Key on the two together when storing rows from this feed.</p>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">Every row carries the fields in the example. What follows them depends on the <code class="doc-inline-code">type</code>: a comment has <code class="doc-inline-code">comment</code>, a photo has <code class="doc-inline-code">photo_url</code>, and a video has <code class="doc-inline-code">youtube_url</code> and <code class="doc-inline-code">embed_url</code>. <code class="doc-inline-code">event_part_id</code> is the agenda part the submission was made on, or <code class="doc-inline-code">null</code>.</p>
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
                <span class="code-string">"event_part_id"</span>: <span class="code-value">null</span>,
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
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            Error Handling
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">The API uses standard HTTP status codes, and every endpoint returns the reason as a JSON <code class="doc-inline-code">error</code> string. The few failures raised outside an endpoint carry <code class="doc-inline-code">message</code> in its place, and need an <code class="doc-inline-code">Accept</code> header to be JSON at all: see <a href="#accept-header" class="doc-link">Ask for JSON</a>.</p>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead><tr><th>Code</th><th>When you see it</th></tr></thead>
                                <tbody>
                                    <tr><td><span class="text-green-700 dark:text-green-400 font-semibold">200</span></td><td>Success</td></tr>
                                    <tr><td><span class="text-green-700 dark:text-green-400 font-semibold">201</span></td><td>Created, returned by Register, Create Schedule, Create Sub-Schedule, Create Event and Create Sale</td></tr>
                                    <tr><td><span class="text-red-700 dark:text-red-400 font-semibold">400</span></td><td>Verification codes requested on a selfhosted install, where they do not apply</td></tr>
                                    <tr><td><span class="text-red-700 dark:text-red-400 font-semibold">401</span></td><td>API key missing, invalid, or past its one-year expiry. Also a wrong email or password on Login.</td></tr>
                                    <tr><td><span class="text-red-700 dark:text-red-400 font-semibold">403</span></td><td>You are not an owner or admin of the record, the schedule is not on a Pro or Enterprise plan, the sale row is a named guest's rather than its group's primary, the account uses two-factor authentication, or selfhosted registration is closed</td></tr>
                                    <tr><td><span class="text-red-700 dark:text-red-400 font-semibold">404</span></td><td>The record does not exist or has been deleted. A schedule your key cannot reach also answers <code class="doc-inline-code">404</code>, where an event or a sale answers <code class="doc-inline-code">403</code>.</td></tr>
                                    <tr><td><span class="text-red-700 dark:text-red-400 font-semibold">409</span></td><td>Login when the account already has an unexpired API key, or a refund whose outcome could not be confirmed or is still in progress</td></tr>
                                    <tr><td><span class="text-red-700 dark:text-red-400 font-semibold">422</span></td><td>Validation error, with field-level detail in <code class="doc-inline-code">errors</code>. Also business refusals such as an unmatched venue, a sold-out ticket, a past event, deleting an event that has sales, or the daily cap on new events.</td></tr>
                                    <tr><td><span class="text-red-700 dark:text-red-400 font-semibold">423</span></td><td>The API key is blocked for 15 minutes after 10 consecutive failed attempts</td></tr>
                                    <tr><td><span class="text-red-700 dark:text-red-400 font-semibold">429</span></td><td>Rate limit exceeded, see <a href="#rate-limits" class="doc-link">Rate Limits</a></td></tr>
                                    <tr><td><span class="text-red-700 dark:text-red-400 font-semibold">500</span></td><td>Server error. Retry with backoff; the failure is logged on our side.</td></tr>
                                    <tr><td><span class="text-red-700 dark:text-red-400 font-semibold">502</span></td><td><a href="#delete-schedule" class="doc-link">Delete Schedule</a> could not cancel the schedule's paid plan, so nothing was deleted</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">A <code class="doc-inline-code">422</code> covers two different things. A schema problem carries an <code class="doc-inline-code">errors</code> object and is worth surfacing field by field; a business refusal carries only <code class="doc-inline-code">error</code> and reads as a sentence. Check for <code class="doc-inline-code">errors</code> before assuming its shape.</p>
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
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                    See Also
                </h2>
                <ul class="doc-list">
                    <li><a href="/api/openapi.json" class="doc-link">OpenAPI Specification</a> - Machine-readable spec for AI agents and code generators</li>
                    <li><a href="/.well-known/agents.json" class="doc-link">agents.json</a> - Named multi-step flows, such as register then create a schedule then add an event</li>
                    <li><a href="{{ route('marketing.docs.developer.webhooks') }}" class="doc-link">Webhooks</a> - Get pushed the sale and event changes instead of polling for them</li>
                    <li><a href="{{ route('marketing.docs.account_settings') }}#api" class="doc-link">Account Settings</a> - Turn on API access and manage your key</li>
                    <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - What each event field means in the admin panel</li>
                    <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - Ticket types, sales and check-in</li>
                </ul>
            </section>
        </div>
    </div></x-docs-page>
