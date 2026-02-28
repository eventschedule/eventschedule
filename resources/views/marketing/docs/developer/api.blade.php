<x-marketing-layout>
    <x-slot name="title">API Reference - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">API Reference</x-slot>
    <x-slot name="description">Programmatically manage schedules and events with the Event Schedule REST API. Learn about authentication, endpoints, and response formats.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "API Reference - Event Schedule",
        "description": "Programmatically manage schedules and events with the Event Schedule REST API. Learn about authentication, endpoints, and response formats.",
        "author": { "@type": "Organization", "name": "Event Schedule" },
        "publisher": { "@type": "Organization", "name": "Event Schedule", "logo": { "@type": "ImageObject", "url": "{{ config('app.url') }}/images/light_logo.png", "width": 712, "height": 140 } },
        "mainEntityOfPage": { "@type": "WebPage", "@id": "{{ url()->current() }}" },
        "datePublished": "2024-01-01",
        "dateModified": "2026-02-01"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <style {!! nonce_attr() !!}>
    /* API two-panel layout */
    .api-endpoint-row { margin-bottom: 0; }
    @media (min-width: 1024px) {
        .api-endpoint-row { display: grid; grid-template-columns: 1fr 380px; gap: 2rem; align-items: start; }
    }
    @media (min-width: 1280px) {
        .api-endpoint-row { grid-template-columns: 1fr 480px; gap: 2.5rem; }
    }
    .api-endpoint-desc { min-width: 0; }
    @media (min-width: 1024px) {
        .api-endpoint-code { position: sticky; top: 5rem; }
    }
    /* Dark column */
    .api-dark-bg { display: none; }
    @media (min-width: 1024px) {
        .api-dark-bg { display: block; position: absolute; top: 0; bottom: 0; right: -2rem; width: calc(380px + 3rem); background: #0d1117; border-left: 1px solid rgba(255,255,255,0.08); }
    }
    @media (min-width: 1280px) {
        .api-dark-bg { width: calc(480px + 3.5rem); }
    }
    /* Force dark code in right column */
    .api-endpoint-code .doc-code-block { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); }
    .api-endpoint-code .doc-code-header { background: rgba(255,255,255,0.05); border-bottom-color: rgba(255,255,255,0.08); color: #9ca3af; }
    .api-endpoint-code .doc-copy-btn { color: #9ca3af; background: rgba(255,255,255,0.1); }
    .api-endpoint-code .doc-copy-btn:hover { background: rgba(255,255,255,0.2); color: white; }
    .api-endpoint-code .doc-code-block code { color: #d1d5db; }
    .api-endpoint-code .code-keyword { color: #c084fc; }
    .api-endpoint-code .code-string { color: #86efac; }
    .api-endpoint-code .code-variable { color: #f472b6; }
    .api-endpoint-code .code-value { color: #60a5fa; }
    .api-endpoint-code .code-comment { color: #6b7280; }
    @media (max-width: 1023px) {
        .api-endpoint-code .doc-code-block { background: #0d1117; border-radius: 0.75rem; }
    }
    /* Section dividers */
    @media (min-width: 1024px) {
        .api-endpoint-section { padding-bottom: 2.5rem; margin-bottom: 2.5rem; }
        .api-endpoint-code { border-top: 1px solid rgba(255,255,255,0.08); padding-top: 1.5rem; }
    }
    .api-main-section { overflow-x: clip; }
    /* Sidebar */
    @media (min-width: 1024px) {
        .api-sidebar { width: 240px; flex-shrink: 0; position: sticky; top: 2rem; max-height: calc(100vh - 4rem); overflow-y: auto; padding-right: 0.5rem; }
        .api-sidebar::-webkit-scrollbar { width: 3px; }
        .api-sidebar::-webkit-scrollbar-thumb { background: rgba(128,128,128,0.2); border-radius: 3px; }
    }
    /* Search */
    .api-search { position: relative; margin-bottom: 1rem; }
    .api-search input { width: 100%; padding: 0.5rem 2rem 0.5rem 0.75rem; border: 1px solid rgba(0,0,0,0.15); border-radius: 0.5rem; font-size: 0.8125rem; background: white; color: #111827; outline: none; }
    .dark .api-search input { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: white; }
    .api-search input:focus { border-color: #3b82f6; }
    .api-search-clear { position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; display: none; font-size: 1rem; line-height: 1; padding: 0.25rem; }
    .api-search-clear.visible { display: block; }
    .api-search-shortcut { position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); font-size: 0.6875rem; color: #9ca3af; border: 1px solid rgba(0,0,0,0.15); border-radius: 0.25rem; padding: 0 0.375rem; line-height: 1.5; pointer-events: none; }
    .dark .api-search-shortcut { border-color: rgba(255,255,255,0.15); }
    /* Accordion */
    .api-nav-group-header { display: flex; align-items: center; justify-content: space-between; width: 100%; padding: 0.375rem 0.5rem; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; background: none; border: none; cursor: pointer; margin-top: 0.75rem; }
    .api-nav-group:first-child .api-nav-group-header { margin-top: 0; }
    .api-nav-group-header:hover { color: #111827; }
    .dark .api-nav-group-header { color: #9ca3af; }
    .dark .api-nav-group-header:hover { color: white; }
    .api-nav-chevron { width: 14px; height: 14px; transition: transform 0.2s; flex-shrink: 0; }
    .api-nav-group.expanded .api-nav-chevron { transform: rotate(90deg); }
    .api-nav-group-items { max-height: 0; overflow: hidden; transition: max-height 0.25s ease; }
    .api-nav-group.expanded .api-nav-group-items { max-height: 600px; }
    .api-nav-group-items .doc-nav-link { display: flex; align-items: center; padding: 0.375rem 0.5rem; font-size: 0.8125rem; border-radius: 0.375rem; color: #6b7280; transition: color 0.15s, background 0.15s; }
    .api-nav-group-items .doc-nav-link:hover { color: #111827; background: rgba(0,0,0,0.04); }
    .dark .api-nav-group-items .doc-nav-link { color: #9ca3af; }
    .dark .api-nav-group-items .doc-nav-link:hover { color: white; background: rgba(255,255,255,0.05); }
    /* Method dots */
    .api-method-dot { width: 6px; height: 6px; border-radius: 50%; margin-right: 0.5rem; flex-shrink: 0; display: inline-block; }
    .api-method-get { background: #3b82f6; }
    .api-method-post { background: #22c55e; }
    .api-method-put { background: #eab308; }
    .api-method-delete { background: #ef4444; }
    /* Mobile drawer */
    @media (max-width: 1023px) {
        .api-sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: 300px; background: white; z-index: 50; transform: translateX(-100%); transition: transform 0.3s ease; overflow-y: auto; padding: 1rem; box-shadow: 2px 0 8px rgba(0,0,0,0.1); }
        .dark .api-sidebar { background: #1e1e1e; }
        .api-sidebar.open { transform: translateX(0); }
    }
    .api-drawer-backdrop { display: none; }
    @media (max-width: 1023px) {
        .api-drawer-backdrop { display: block; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 49; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
        .api-drawer-backdrop.open { opacity: 1; pointer-events: auto; }
    }
    .api-mobile-menu-btn { display: none; }
    @media (max-width: 1023px) {
        .api-mobile-menu-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border: 1px solid rgba(0,0,0,0.15); border-radius: 0.5rem; background: white; color: #374151; font-size: 0.875rem; cursor: pointer; margin-bottom: 1.5rem; }
        .dark .api-mobile-menu-btn { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: #d1d5db; }
    }
    .api-drawer-close { display: none; }
    @media (max-width: 1023px) {
        .api-drawer-close { display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border: none; background: none; color: #6b7280; cursor: pointer; font-size: 1.25rem; margin-left: auto; margin-bottom: 0.5rem; }
    }
    /* Print */
    @media print {
        .api-dark-bg, .api-sidebar, .api-mobile-menu-btn, .api-drawer-backdrop { display: none !important; }
        .api-endpoint-row { display: block !important; }
        .api-endpoint-code .doc-code-block { background: #f3f4f6 !important; border: 1px solid #e5e7eb !important; }
        .api-endpoint-code .doc-code-block code { color: #374151 !important; }
    }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>
        <div class="relative z-10 max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="API Reference" />
            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">API Reference</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Programmatically manage schedules and events with the Event Schedule REST API. Write operations require a Pro plan. A machine-readable <a href="/api/openapi.json" class="text-cyan-400 hover:text-cyan-300">OpenAPI spec</a> is also available.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="api-main-section bg-white dark:bg-[#0a0a0f] py-12">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            <button class="api-mobile-menu-btn" id="apiMobileMenuBtn">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                API Navigation
            </button>
            <div class="api-drawer-backdrop" id="apiDrawerBackdrop"></div>

            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar -->
                <aside class="api-sidebar" id="apiSidebar">
                    <button class="api-drawer-close" id="apiDrawerClose">&times;</button>
                    <div class="api-search">
                        <input type="text" id="apiSearch" placeholder="Search endpoints...">
                        <span class="api-search-shortcut" id="apiSearchShortcut">/</span>
                        <button class="api-search-clear" id="apiSearchClear">&times;</button>
                    </div>
                    <nav>
                        <div class="api-nav-group expanded" data-group="getting-started">
                            <button class="api-nav-group-header">Getting Started <svg class="api-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></button>
                            <div class="api-nav-group-items">
                                <a href="#authentication" class="doc-nav-link" data-search="authentication api key header x-api-key">Authentication</a>
                                <a href="#rate-limits" class="doc-nav-link" data-search="rate limits throttle 429">Rate Limits</a>
                                <a href="#response-format" class="doc-nav-link" data-search="response format json data meta error">Response Format</a>
                                <a href="#pagination" class="doc-nav-link" data-search="pagination page per_page">Pagination</a>
                            </div>
                        </div>
                        <div class="api-nav-group" data-group="auth">
                            <button class="api-nav-group-header">Auth <svg class="api-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></button>
                            <div class="api-nav-group-items">
                                <a href="#register" class="doc-nav-link" data-search="register post /api/register account create"><span class="api-method-dot api-method-post"></span>Register</a>
                                <a href="#login" class="doc-nav-link" data-search="login post /api/login authenticate"><span class="api-method-dot api-method-post"></span>Login</a>
                            </div>
                        </div>
                        <div class="api-nav-group" data-group="schedules">
                            <button class="api-nav-group-header">Schedules <svg class="api-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></button>
                            <div class="api-nav-group-items">
                                <a href="#list-schedules" class="doc-nav-link" data-search="list schedules get /api/schedules filter name type"><span class="api-method-dot api-method-get"></span>List Schedules</a>
                                <a href="#show-schedule" class="doc-nav-link" data-search="show schedule get /api/schedules subdomain"><span class="api-method-dot api-method-get"></span>Show Schedule</a>
                                <a href="#create-schedule" class="doc-nav-link" data-search="create schedule post /api/schedules venue talent curator"><span class="api-method-dot api-method-post"></span>Create Schedule</a>
                                <a href="#update-schedule" class="doc-nav-link" data-search="update schedule put /api/schedules"><span class="api-method-dot api-method-put"></span>Update Schedule</a>
                                <a href="#delete-schedule" class="doc-nav-link" data-search="delete schedule /api/schedules"><span class="api-method-dot api-method-delete"></span>Delete Schedule</a>
                            </div>
                        </div>
                        <div class="api-nav-group" data-group="sub-schedules">
                            <button class="api-nav-group-header">Sub-Schedules <svg class="api-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></button>
                            <div class="api-nav-group-items">
                                <a href="#list-groups" class="doc-nav-link" data-search="list sub-schedules groups get /api/schedules/groups"><span class="api-method-dot api-method-get"></span>List Sub-Schedules</a>
                                <a href="#create-group" class="doc-nav-link" data-search="create sub-schedule group post /api/schedules/groups"><span class="api-method-dot api-method-post"></span>Create Sub-Schedule</a>
                                <a href="#update-group" class="doc-nav-link" data-search="update sub-schedule group put /api/schedules/groups"><span class="api-method-dot api-method-put"></span>Update Sub-Schedule</a>
                                <a href="#delete-group" class="doc-nav-link" data-search="delete sub-schedule group /api/schedules/groups"><span class="api-method-dot api-method-delete"></span>Delete Sub-Schedule</a>
                            </div>
                        </div>
                        <div class="api-nav-group" data-group="events">
                            <button class="api-nav-group-header">Events <svg class="api-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></button>
                            <div class="api-nav-group-items">
                                <a href="#list-events" class="doc-nav-link" data-search="list events get /api/events filter subdomain date"><span class="api-method-dot api-method-get"></span>List Events</a>
                                <a href="#show-event" class="doc-nav-link" data-search="show event get /api/events detail"><span class="api-method-dot api-method-get"></span>Show Event</a>
                                <a href="#create-event" class="doc-nav-link" data-search="create event post /api/events tickets agenda"><span class="api-method-dot api-method-post"></span>Create Event</a>
                                <a href="#update-event" class="doc-nav-link" data-search="update event put /api/events partial"><span class="api-method-dot api-method-put"></span>Update Event</a>
                                <a href="#delete-event" class="doc-nav-link" data-search="delete event /api/events"><span class="api-method-dot api-method-delete"></span>Delete Event</a>
                                <a href="#upload-flyer" class="doc-nav-link" data-search="upload flyer image post /api/events/flyer multipart"><span class="api-method-dot api-method-post"></span>Upload Flyer</a>
                                <a href="#list-categories" class="doc-nav-link" data-search="list categories get /api/categories"><span class="api-method-dot api-method-get"></span>List Categories</a>
                            </div>
                        </div>
                        <div class="api-nav-group" data-group="sales">
                            <button class="api-nav-group-header">Sales <svg class="api-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></button>
                            <div class="api-nav-group-items">
                                <a href="#list-sales" class="doc-nav-link" data-search="list sales get /api/sales filter status email"><span class="api-method-dot api-method-get"></span>List Sales</a>
                                <a href="#show-sale" class="doc-nav-link" data-search="show sale get /api/sales detail"><span class="api-method-dot api-method-get"></span>Show Sale</a>
                                <a href="#create-sale" class="doc-nav-link" data-search="create sale post /api/sales tickets"><span class="api-method-dot api-method-post"></span>Create Sale</a>
                                <a href="#update-sale" class="doc-nav-link" data-search="update sale status put /api/sales mark_paid refund cancel"><span class="api-method-dot api-method-put"></span>Update Sale Status</a>
                                <a href="#delete-sale" class="doc-nav-link" data-search="delete sale /api/sales"><span class="api-method-dot api-method-delete"></span>Delete Sale</a>
                            </div>
                        </div>
                        <div class="api-nav-group" data-group="reference">
                            <button class="api-nav-group-header">Reference <svg class="api-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></button>
                            <div class="api-nav-group-items">
                                <a href="#error-handling" class="doc-nav-link" data-search="error handling status codes 401 403 404 422 429 500">Error Handling</a>
                                <a href="#see-also" class="doc-nav-link" data-search="see also resources links openapi">See Also</a>
                            </div>
                        </div>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="api-content-wrapper relative">
                        <div class="api-dark-bg" aria-hidden="true"></div>
                        <div class="prose-dark relative z-[1]">
                            <!-- Authentication -->
                            <section id="authentication" class="doc-section api-endpoint-section">
                                <div class="api-endpoint-row">
                                    <div class="api-endpoint-desc">
                                        <h2 class="doc-heading">Authentication</h2>
                                        <p class="text-gray-600 dark:text-gray-300 mb-6">Most API endpoints require authentication via an API key in the <code class="doc-inline-code">X-API-Key</code> header. You can get an API key by:</p>
                                        <ul class="doc-list mb-6">
                                            <li>Using the <a href="#register" class="text-cyan-400 hover:text-cyan-300">Register</a> or <a href="#login" class="text-cyan-400 hover:text-cyan-300">Login</a> endpoints (for AI agents)</li>
                                            <li>Generating one from your <a href="{{ route('marketing.docs.account_settings') }}#api" class="text-cyan-400 hover:text-cyan-300">account settings</a> (for manual use)</li>
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
                                        <h2 class="doc-heading">Rate Limits</h2>
                                        <p class="text-gray-600 dark:text-gray-300 mb-6">API requests are rate limited per IP address:</p>
                                        <div class="overflow-x-auto mb-6">
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
                                        <h2 class="doc-heading">Response Format</h2>
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
                                        <h2 class="doc-heading">Pagination</h2>
                                        <p class="text-gray-600 dark:text-gray-300 mb-6">List endpoints support pagination through query parameters:</p>
                                        <div class="overflow-x-auto mb-6">
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
                                        <h2 class="doc-heading">Register</h2>
                                        @if(config('app.hosted'))
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">Registration is a two-step process: first send a verification code, then register with it.</p>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Step 1: Send Verification Code</h3>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                            <code class="doc-inline-code">/api/register/send-code</code>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">No authentication required. Rate limited to 5 codes per email per hour.</p>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Step 2: Register</h3>
                                        @else
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">Create a new account and receive an API key.</p>
                                        @endif
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                            <code class="doc-inline-code">/api/register</code>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">No authentication required. Rate limited to 3 registrations per IP per hour.</p>
                                        <div class="overflow-x-auto mb-6">
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
                                        <h2 class="doc-heading">Login</h2>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                            <code class="doc-inline-code">/api/login</code>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">No authentication required. Returns a new API key on success.</p>
                                        <div class="doc-callout doc-callout-warning">
                                            <div class="doc-callout-title">Important</div>
                                            <p>Login generates a new API key each time, replacing any existing key. Store the key securely and avoid calling login repeatedly. Accounts with two-factor authentication must generate API keys from the web UI.</p>
                                        </div>
                                        <div class="overflow-x-auto mb-6">
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
                                        <h2 class="doc-heading">List Schedules</h2>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                                            <code class="doc-inline-code">/api/schedules</code>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of your schedules. Supports filtering:</p>
                                        <div class="overflow-x-auto mb-6">
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
                                        <h2 class="doc-heading">Show Schedule</h2>
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
                                        <h2 class="doc-heading">Create Schedule</h2>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                            <code class="doc-inline-code">/api/schedules</code>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-300 mb-6">Create a new schedule. New accounts in hosted mode automatically get a Pro trial.</p>
                                        <div class="overflow-x-auto mb-6">
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
                                        <h2 class="doc-heading">Update Schedule</h2>
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
                                        <h2 class="doc-heading">Delete Schedule</h2>
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
                                        <h2 class="doc-heading">List Sub-Schedules</h2>
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
                                        <h2 class="doc-heading">Create Sub-Schedule</h2>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                            <code class="doc-inline-code">/api/schedules/{subdomain}/groups</code>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-300 mb-6">Create a sub-schedule within a schedule. Requires Pro plan. Slug is auto-generated from the name. Names are auto-translated if the schedule language is not English.</p>
                                        <div class="overflow-x-auto mb-6">
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
                                        <h2 class="doc-heading">Update Sub-Schedule</h2>
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
                                        <h2 class="doc-heading">Delete Sub-Schedule</h2>
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
                                        <h2 class="doc-heading">List Events</h2>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                                            <code class="doc-inline-code">/api/events</code>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of your events, sorted by start date (newest first). Supports filtering:</p>
                                        <div class="overflow-x-auto mb-6">
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
                                        <h2 class="doc-heading">Show Event</h2>
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
                                        <h2 class="doc-heading">Create Event</h2>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                            <code class="doc-inline-code">/api/events/{subdomain}</code>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-300 mb-6">Create a new event on a schedule. Requires Pro plan and owner or admin access.</p>
                                        <div class="overflow-x-auto mb-6">
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
                                                    <tr><td><code class="doc-inline-code">category_id</code></td><td>No</td><td>Category ID (see <a href="#list-categories" class="text-cyan-400 hover:text-cyan-300">List Categories</a>)</td></tr>
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
                                        <h2 class="doc-heading">Update Event</h2>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="bg-yellow-600 text-white px-2 py-1 rounded text-sm font-medium">PUT</span>
                                            <code class="doc-inline-code">/api/events/{id}</code>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-300 mb-6">Update an event. Supports partial updates - only include the fields you want to change. Recurring configuration, tickets, and agenda parts are preserved when not included in the request. Requires Pro plan.</p>
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
                                        <h2 class="doc-heading">Delete Event</h2>
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
                                        <h2 class="doc-heading">Upload Flyer</h2>
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
                                        <h2 class="doc-heading">List Categories</h2>
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
                                        <h2 class="doc-heading">List Sales</h2>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                                            <code class="doc-inline-code">/api/sales</code>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of sales for events you own or administer. Supports filtering:</p>
                                        <div class="overflow-x-auto mb-6">
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
                                        <h2 class="doc-heading">Show Sale</h2>
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
                                        <h2 class="doc-heading">Create Sale</h2>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                            <code class="doc-inline-code">/api/sales</code>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-300 mb-6">Create a new sale manually for an event. Sales are created as unpaid (free tickets are auto-marked as paid).</p>
                                        <div class="overflow-x-auto mb-6">
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
                                        <h2 class="doc-heading">Update Sale Status</h2>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="bg-yellow-600 text-white px-2 py-1 rounded text-sm font-medium">PUT</span>
                                            <code class="doc-inline-code">/api/sales/{id}</code>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-300 mb-6">Perform a status action on a sale. Available actions depend on the current status.</p>
                                        <div class="overflow-x-auto mb-6">
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
                                        <h2 class="doc-heading">Delete Sale</h2>
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

                            <!-- Error Handling -->
                            <section id="error-handling" class="doc-section api-endpoint-section">
                                <div class="api-endpoint-row">
                                    <div class="api-endpoint-desc">
                                        <h2 class="doc-heading">Error Handling</h2>
                                        <p class="text-gray-600 dark:text-gray-300 mb-6">The API uses standard HTTP status codes and returns error messages in JSON format.</p>
                                        <div class="overflow-x-auto mb-6">
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
                                <h2 class="doc-heading">See Also</h2>
                                <ul class="doc-list">
                                    <li><a href="/api/openapi.json" class="text-cyan-400 hover:text-cyan-300">OpenAPI Specification</a> - Machine-readable API spec for AI agents and code generators</li>
                                    <li><a href="{{ route('marketing.docs.account_settings') }}#api" class="text-cyan-400 hover:text-cyan-300">Account Settings</a> - Enable API and manage your API key</li>
                                    <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Understand event fields and details</li>
                                    <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Selling Tickets</a> - Understand tickets and sales</li>
                                </ul>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')

    <script {!! nonce_attr() !!}>
    (function() {
        // Accordion toggle
        document.querySelectorAll('.api-nav-group-header').forEach(function(btn) {
            btn.addEventListener('click', function() {
                this.closest('.api-nav-group').classList.toggle('expanded');
            });
        });

        // Search
        var searchInput = document.getElementById('apiSearch');
        var searchClear = document.getElementById('apiSearchClear');
        var searchShortcut = document.getElementById('apiSearchShortcut');
        var navGroups = document.querySelectorAll('.api-nav-group');

        searchInput.addEventListener('input', function() {
            var query = this.value.toLowerCase().trim();
            searchClear.classList.toggle('visible', query.length > 0);
            searchShortcut.style.display = query.length > 0 ? 'none' : '';
            navGroups.forEach(function(group) {
                var links = group.querySelectorAll('.doc-nav-link');
                var anyVisible = false;
                links.forEach(function(link) {
                    var text = (link.textContent + ' ' + (link.dataset.search || '')).toLowerCase();
                    var match = !query || text.indexOf(query) !== -1;
                    link.style.display = match ? '' : 'none';
                    if (match) anyVisible = true;
                });
                group.style.display = anyVisible || !query ? '' : 'none';
                if (query && anyVisible) group.classList.add('expanded');
            });
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                var visible = document.querySelectorAll('.api-nav-group-items .doc-nav-link:not([style*="display: none"])');
                if (visible.length === 1) { visible[0].click(); searchInput.value = ''; searchInput.dispatchEvent(new Event('input')); }
            }
            if (e.key === 'Escape') { searchInput.value = ''; searchInput.dispatchEvent(new Event('input')); searchInput.blur(); }
        });

        searchClear.addEventListener('click', function() { searchInput.value = ''; searchInput.dispatchEvent(new Event('input')); searchInput.focus(); });

        // Keyboard shortcut: / to focus search
        document.addEventListener('keydown', function(e) {
            if (e.key === '/' && !e.ctrlKey && !e.metaKey && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                searchInput.focus();
            }
        });

        // Mobile drawer
        var sidebar = document.getElementById('apiSidebar');
        var backdrop = document.getElementById('apiDrawerBackdrop');
        var menuBtn = document.getElementById('apiMobileMenuBtn');
        var closeBtn = document.getElementById('apiDrawerClose');
        function openDrawer() { sidebar.classList.add('open'); backdrop.classList.add('open'); document.body.style.overflow = 'hidden'; }
        function closeDrawer() { sidebar.classList.remove('open'); backdrop.classList.remove('open'); document.body.style.overflow = ''; }
        menuBtn.addEventListener('click', openDrawer);
        closeBtn.addEventListener('click', closeDrawer);
        backdrop.addEventListener('click', closeDrawer);
        sidebar.querySelectorAll('.doc-nav-link').forEach(function(link) {
            link.addEventListener('click', function() { if (window.innerWidth < 1024) closeDrawer(); });
        });
    })();
    </script>
</x-marketing-layout>
