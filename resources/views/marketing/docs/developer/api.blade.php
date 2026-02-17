<x-marketing-layout>
    <x-slot name="title">API Reference - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">API Reference</x-slot>
    <x-slot name="description">Programmatically manage schedules and events with the Event Schedule REST API. Learn about authentication, endpoints, and response formats.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "API Reference - Event Schedule",
        "description": "Programmatically manage schedules and events with the Event Schedule REST API. Learn about authentication, endpoints, and response formats.",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ config('app.url') }}/images/light_logo.png"
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}"
        }
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
    <section class="bg-white dark:bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Getting Started</div>
                        <a href="#authentication" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Authentication</a>
                        <a href="#rate-limits" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Rate Limits</a>
                        <a href="#response-format" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Response Format</a>
                        <a href="#pagination" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Pagination</a>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 mt-6">Auth</div>
                        <a href="#register" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Register</a>
                        <a href="#login" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Login</a>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 mt-6">Schedules</div>
                        <a href="#list-schedules" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">List Schedules</a>
                        <a href="#show-schedule" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Show Schedule</a>
                        <a href="#create-schedule" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Create Schedule</a>
                        <a href="#update-schedule" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Update Schedule</a>
                        <a href="#delete-schedule" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Delete Schedule</a>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 mt-6">Sub-Schedules</div>
                        <a href="#list-groups" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">List Sub-Schedules</a>
                        <a href="#create-group" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Create Sub-Schedule</a>
                        <a href="#update-group" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Update Sub-Schedule</a>
                        <a href="#delete-group" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Delete Sub-Schedule</a>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 mt-6">Events</div>
                        <a href="#list-events" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">List Events</a>
                        <a href="#show-event" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Show Event</a>
                        <a href="#create-event" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Create Event</a>
                        <a href="#update-event" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Update Event</a>
                        <a href="#delete-event" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Delete Event</a>
                        <a href="#upload-flyer" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Upload Flyer</a>
                        <a href="#list-categories" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">List Categories</a>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 mt-6">Sales</div>
                        <a href="#list-sales" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">List Sales</a>
                        <a href="#show-sale" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Show Sale</a>
                        <a href="#create-sale" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Create Sale</a>
                        <a href="#update-sale" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Update Sale</a>
                        <a href="#delete-sale" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Delete Sale</a>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 mt-6">Reference</div>
                        <a href="#error-handling" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Error Handling</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Authentication -->
                        <section id="authentication" class="doc-section">
                            <h2 class="doc-heading">Authentication</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Most API endpoints require authentication via an API key in the <code class="doc-inline-code">X-API-Key</code> header. You can get an API key by:</p>
                            <ul class="doc-list mb-6">
                                <li>Using the <a href="#register" class="text-cyan-400 hover:text-cyan-300">Register</a> or <a href="#login" class="text-cyan-400 hover:text-cyan-300">Login</a> endpoints (for AI agents)</li>
                                <li>Generating one from your <a href="{{ route('marketing.docs.account_settings') }}#api" class="text-cyan-400 hover:text-cyan-300">account settings</a> (for manual use)</li>
                            </ul>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL Example</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/schedules"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">API Key Security</div>
                                <p>Keep your API key secure and never expose it in client-side code. API keys expire after 1 year. Login generates a new key each time, replacing any existing key.</p>
                            </div>
                        </section>

                        <!-- Rate Limits -->
                        <section id="rate-limits" class="doc-section">
                            <h2 class="doc-heading">Rate Limits</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">API requests are rate limited per IP address:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Operation Type</th>
                                            <th>Limit</th>
                                            <th>HTTP Methods</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Read operations</td>
                                            <td>300 requests/minute</td>
                                            <td>GET</td>
                                        </tr>
                                        <tr>
                                            <td>Write operations</td>
                                            <td>30 requests/minute</td>
                                            <td>POST, PUT, DELETE</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300">Auth endpoints (register, login) have separate per-endpoint rate limits. When rate limited, the API returns a <code class="doc-inline-code">429</code> status code.</p>
                        </section>

                        <!-- Response Format -->
                        <section id="response-format" class="doc-section">
                            <h2 class="doc-heading">Response Format</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Successful responses wrap results in a <code class="doc-inline-code">data</code> property. List endpoints include a <code class="doc-inline-code">meta</code> object with pagination. Error responses use an <code class="doc-inline-code">error</code> property.</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>Success Response</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code>{
    <span class="code-string">"data"</span>: [...],
    <span class="code-string">"meta"</span>: {
        <span class="code-string">"current_page"</span>: <span class="code-value">1</span>,
        <span class="code-string">"total"</span>: <span class="code-value">50</span>
    }
}</code></pre>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>Error Response</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code>{
    <span class="code-string">"error"</span>: <span class="code-string">"Validation failed"</span>,
    <span class="code-string">"errors"</span>: {
        <span class="code-string">"name"</span>: [<span class="code-string">"The name field is required."</span>]
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
                                            <td>Number of items per page (max: 500)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Register -->
                        <section id="register" class="doc-section">
                            <h2 class="doc-heading">Register</h2>

                            @if(config('app.hosted'))
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Registration is a two-step process: first send a verification code, then register with it.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Step 1: Send Verification Code</h3>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                <code class="doc-inline-code">/api/register/send-code</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">No authentication required. Rate limited to 5 codes per email per hour.</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/register/send-code"</span> \
     -H <span class="code-string">"Content-Type: application/json"</span> \
     -d <span class="code-string">'{"email": "user@example.com"}'</span></code></pre>
                            </div>

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
                                    <thead>
                                        <tr>
                                            <th>Parameter</th>
                                            <th>Required</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">name</code></td>
                                            <td>Yes</td>
                                            <td>Your display name</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">email</code></td>
                                            <td>Yes</td>
                                            <td>Email address</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">password</code></td>
                                            <td>Yes</td>
                                            <td>Password (min 8 characters)</td>
                                        </tr>
                                        @if(config('app.hosted'))
                                        <tr>
                                            <td><code class="doc-inline-code">verification_code</code></td>
                                            <td>Yes</td>
                                            <td>6-digit code from Step 1</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><code class="doc-inline-code">timezone</code></td>
                                            <td>No</td>
                                            <td>Timezone (default: America/New_York)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">language_code</code></td>
                                            <td>No</td>
                                            <td>Language code (default: en)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>Response (201)</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code>{
    <span class="code-string">"data"</span>: {
        <span class="code-string">"api_key"</span>: <span class="code-string">"your_new_api_key"</span>,
        <span class="code-string">"user"</span>: {
            <span class="code-string">"id"</span>: <span class="code-value">1</span>,
            <span class="code-string">"name"</span>: <span class="code-string">"Your Name"</span>,
            <span class="code-string">"email"</span>: <span class="code-string">"user@example.com"</span>
        }
    }
}</code></pre>
                            </div>
                        </section>

                        <!-- Login -->
                        <section id="login" class="doc-section">
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
                                    <thead>
                                        <tr>
                                            <th>Parameter</th>
                                            <th>Required</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">email</code></td>
                                            <td>Yes</td>
                                            <td>Email address</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">password</code></td>
                                            <td>Yes</td>
                                            <td>Password</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/login"</span> \
     -H <span class="code-string">"Content-Type: application/json"</span> \
     -d <span class="code-string">'{"email": "user@example.com", "password": "your_password"}'</span></code></pre>
                            </div>
                        </section>

                        <!-- List Schedules -->
                        <section id="list-schedules" class="doc-section">
                            <h2 class="doc-heading">List Schedules</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                                <code class="doc-inline-code">/api/schedules</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of your schedules. Supports filtering by <code class="doc-inline-code">name</code> and <code class="doc-inline-code">type</code> query parameters.</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/schedules?type=venue"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                            </div>
                        </section>

                        <!-- Show Schedule -->
                        <section id="show-schedule" class="doc-section">
                            <h2 class="doc-heading">Show Schedule</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                                <code class="doc-inline-code">/api/schedules/{subdomain}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a single schedule by subdomain, including its sub-schedules. Requires owner or admin access.</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/schedules/my-venue"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                            </div>
                        </section>

                        <!-- Create Schedule -->
                        <section id="create-schedule" class="doc-section">
                            <h2 class="doc-heading">Create Schedule</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                <code class="doc-inline-code">/api/schedules</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Create a new schedule. New accounts in hosted mode automatically get a Pro trial.</p>

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
                                            <td><code class="doc-inline-code">name</code></td>
                                            <td>Yes</td>
                                            <td>Schedule name (max 255 characters)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">type</code></td>
                                            <td>Yes</td>
                                            <td>Schedule type: <code class="doc-inline-code">venue</code>, <code class="doc-inline-code">talent</code>, or <code class="doc-inline-code">curator</code></td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">email</code></td>
                                            <td>No</td>
                                            <td>Contact email</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">description</code></td>
                                            <td>No</td>
                                            <td>Markdown description</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">timezone</code></td>
                                            <td>No</td>
                                            <td>Timezone (e.g., America/New_York)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">language_code</code></td>
                                            <td>No</td>
                                            <td>Language code (e.g., en, es, fr)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">website</code></td>
                                            <td>No</td>
                                            <td>Website URL</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">address1</code>, <code class="doc-inline-code">city</code>, <code class="doc-inline-code">state</code>, <code class="doc-inline-code">postal_code</code>, <code class="doc-inline-code">country_code</code></td>
                                            <td>No</td>
                                            <td>Venue address fields (for venue type)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/schedules"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
     -H <span class="code-string">"Content-Type: application/json"</span> \
     -d <span class="code-string">'{"name": "My Venue", "type": "venue", "city": "New York"}'</span></code></pre>
                            </div>
                        </section>

                        <!-- Update Schedule -->
                        <section id="update-schedule" class="doc-section">
                            <h2 class="doc-heading">Update Schedule</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-yellow-600 text-white px-2 py-1 rounded text-sm font-medium">PUT</span>
                                <code class="doc-inline-code">/api/schedules/{subdomain}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Update a schedule. Only include fields you want to change. Requires Pro plan and owner or admin access. Subdomain cannot be changed via API.</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X PUT <span class="code-string">"{{ config('app.url') }}/api/schedules/my-venue"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
     -H <span class="code-string">"Content-Type: application/json"</span> \
     -d <span class="code-string">'{"name": "Updated Name", "description": "New description"}'</span></code></pre>
                            </div>
                        </section>

                        <!-- Delete Schedule -->
                        <section id="delete-schedule" class="doc-section">
                            <h2 class="doc-heading">Delete Schedule</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-red-600 text-white px-2 py-1 rounded text-sm font-medium">DELETE</span>
                                <code class="doc-inline-code">/api/schedules/{subdomain}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Permanently delete a schedule and all associated data. Requires schedule owner access (not just admin).</p>
                        </section>

                        <!-- List Sub-Schedules -->
                        <section id="list-groups" class="doc-section">
                            <h2 class="doc-heading">List Sub-Schedules</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                                <code class="doc-inline-code">/api/schedules/{subdomain}/groups</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">List all sub-schedules for a schedule. Returns id, name, slug, and color for each.</p>
                        </section>

                        <!-- Create Sub-Schedule -->
                        <section id="create-group" class="doc-section">
                            <h2 class="doc-heading">Create Sub-Schedule</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                <code class="doc-inline-code">/api/schedules/{subdomain}/groups</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Create a sub-schedule within a schedule. Requires Pro plan. Slug is auto-generated from the name. Names are auto-translated if the schedule language is not English.</p>

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
                                            <td><code class="doc-inline-code">name</code></td>
                                            <td>Yes</td>
                                            <td>Sub-schedule name</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">color</code></td>
                                            <td>No</td>
                                            <td>Display color (e.g., #FF5733)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Update Sub-Schedule -->
                        <section id="update-group" class="doc-section">
                            <h2 class="doc-heading">Update Sub-Schedule</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-yellow-600 text-white px-2 py-1 rounded text-sm font-medium">PUT</span>
                                <code class="doc-inline-code">/api/schedules/{subdomain}/groups/{group_id}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Update a sub-schedule's name or color. Requires Pro plan.</p>
                        </section>

                        <!-- Delete Sub-Schedule -->
                        <section id="delete-group" class="doc-section">
                            <h2 class="doc-heading">Delete Sub-Schedule</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-red-600 text-white px-2 py-1 rounded text-sm font-medium">DELETE</span>
                                <code class="doc-inline-code">/api/schedules/{subdomain}/groups/{group_id}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Delete a sub-schedule. Events assigned to this sub-schedule will have their sub-schedule reference cleared. Requires Pro plan.</p>
                        </section>

                        <!-- List Events -->
                        <section id="list-events" class="doc-section">
                            <h2 class="doc-heading">List Events</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                                <code class="doc-inline-code">/api/events</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of your events, sorted by start date (newest first). Supports filtering:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Parameter</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">subdomain</code></td>
                                            <td>Filter events by schedule subdomain</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">starts_after</code></td>
                                            <td>Events starting after this date (Y-m-d)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">starts_before</code></td>
                                            <td>Events starting before this date (Y-m-d)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">venue_id</code></td>
                                            <td>Filter by venue (encoded venue schedule ID)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">category_id</code></td>
                                            <td>Filter by category ID</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">name</code></td>
                                            <td>Filter by event name (partial match)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">schedule_type</code></td>
                                            <td>Filter by type: <code class="doc-inline-code">single</code> or <code class="doc-inline-code">recurring</code></td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">tickets_enabled</code></td>
                                            <td>Filter by whether tickets are enabled (boolean)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">group_id</code></td>
                                            <td>Filter by sub-schedule (encoded sub-schedule ID)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/events?subdomain=my-venue&starts_after=2025-01-01"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                            </div>
                        </section>

                        <!-- Show Event -->
                        <section id="show-event" class="doc-section">
                            <h2 class="doc-heading">Show Event</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                                <code class="doc-inline-code">/api/events/{id}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a single event by encoded ID, including tickets, members, and agenda parts.</p>
                        </section>

                        <!-- Create Event -->
                        <section id="create-event" class="doc-section">
                            <h2 class="doc-heading">Create Event</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                <code class="doc-inline-code">/api/events/{subdomain}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Create a new event on a schedule. Requires Pro plan and owner or admin access.</p>

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
                                            <td><code class="doc-inline-code">name</code></td>
                                            <td>Yes</td>
                                            <td>Event name</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">starts_at</code></td>
                                            <td>Yes</td>
                                            <td>Start date/time in your timezone (Y-m-d H:i:s)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">duration</code></td>
                                            <td>No</td>
                                            <td>Duration in hours (0 to 24)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">description</code></td>
                                            <td>No</td>
                                            <td>Full description (Markdown supported, max 10000 characters)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">short_description</code></td>
                                            <td>No</td>
                                            <td>Short description (max 500 characters)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">event_url</code></td>
                                            <td>No</td>
                                            <td>Event or livestream URL</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">registration_url</code></td>
                                            <td>No</td>
                                            <td>External registration URL</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">event_password</code></td>
                                            <td>No</td>
                                            <td>Password to protect the event page</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">category_id</code></td>
                                            <td>No</td>
                                            <td>Category ID (see <a href="#list-categories" class="text-cyan-400 hover:text-cyan-300">List Categories</a>)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">category</code></td>
                                            <td>No</td>
                                            <td>Category name (alternative to category_id, matched via slug)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">schedule</code></td>
                                            <td>No</td>
                                            <td>Sub-schedule slug to assign the event to</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">recurring_interval</code></td>
                                            <td>No</td>
                                            <td>Week interval for every_n_weeks frequency (min 2)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">recurring_end_type</code></td>
                                            <td>No</td>
                                            <td>How recurrence ends: never, on_date, or after_events</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">recurring_end_value</code></td>
                                            <td>No</td>
                                            <td>End date (Y-m-d) or count, based on recurring_end_type</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">schedule_type</code></td>
                                            <td>No</td>
                                            <td><code class="doc-inline-code">single</code> or <code class="doc-inline-code">recurring</code></td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">recurring_frequency</code></td>
                                            <td>No</td>
                                            <td>For recurring: daily, weekly, every_n_weeks, monthly_date, monthly_weekday, yearly</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">tickets_enabled</code></td>
                                            <td>No</td>
                                            <td>Enable ticketing (boolean)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">ticket_currency_code</code></td>
                                            <td>No</td>
                                            <td>3-letter ISO currency code (e.g., USD)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">payment_method</code></td>
                                            <td>No</td>
                                            <td>Payment method: stripe, invoiceninja, payment_url, or manual</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">payment_instructions</code></td>
                                            <td>No</td>
                                            <td>Payment instructions (max 5000 characters)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">tickets</code></td>
                                            <td>No</td>
                                            <td>Array of ticket types: [{type, quantity, price, description}]</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">event_parts</code></td>
                                            <td>No</td>
                                            <td>Agenda parts: [{name, description, start_time, end_time}]</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">members</code></td>
                                            <td>No</td>
                                            <td>Array of performers: [{name, email}]</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">venue_id</code></td>
                                            <td>No</td>
                                            <td>Encoded venue schedule ID</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">venue_name</code></td>
                                            <td>No</td>
                                            <td>Venue name (with venue_address1 for auto-matching)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">venue_address1</code></td>
                                            <td>No</td>
                                            <td>Venue address (with venue_name for auto-matching)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
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
                        </section>

                        <!-- Update Event -->
                        <section id="update-event" class="doc-section">
                            <h2 class="doc-heading">Update Event</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-yellow-600 text-white px-2 py-1 rounded text-sm font-medium">PUT</span>
                                <code class="doc-inline-code">/api/events/{id}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Update an event. Supports partial updates - only include the fields you want to change. Recurring configuration, tickets, and agenda parts are preserved when not included in the request. Requires Pro plan.</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X PUT <span class="code-string">"{{ config('app.url') }}/api/events/abc123"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
     -H <span class="code-string">"Content-Type: application/json"</span> \
     -d <span class="code-string">'{"name": "Updated Jazz Night", "duration": 4}'</span></code></pre>
                            </div>
                        </section>

                        <!-- Delete Event -->
                        <section id="delete-event" class="doc-section">
                            <h2 class="doc-heading">Delete Event</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-red-600 text-white px-2 py-1 rounded text-sm font-medium">DELETE</span>
                                <code class="doc-inline-code">/api/events/{id}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Permanently delete an event. This also removes it from Google Calendar and CalDAV if synced.</p>
                        </section>

                        <!-- Upload Flyer -->
                        <section id="upload-flyer" class="doc-section">
                            <h2 class="doc-heading">Upload Flyer</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                <code class="doc-inline-code">/api/events/flyer/{event_id}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Upload a flyer image for an event. Send as multipart form data with a <code class="doc-inline-code">flyer_image</code> field.</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/events/flyer/abc123"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
     -F <span class="code-string">"flyer_image=@/path/to/flyer.jpg"</span></code></pre>
                            </div>
                        </section>

                        <!-- List Categories -->
                        <section id="list-categories" class="doc-section">
                            <h2 class="doc-heading">List Categories</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                                <code class="doc-inline-code">/api/categories</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Returns all available event categories with their IDs and names. Use the <code class="doc-inline-code">id</code> when creating or updating events.</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>Response</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
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
                        </section>

                        <!-- List Sales -->
                        <section id="list-sales" class="doc-section">
                            <h2 class="doc-heading">List Sales</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                                <code class="doc-inline-code">/api/sales</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a paginated list of sales for events you own or administer. Supports filtering:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Parameter</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">event_id</code></td>
                                            <td>Filter by event (encoded event ID)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">subdomain</code></td>
                                            <td>Filter by schedule subdomain</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">status</code></td>
                                            <td>Filter by status: unpaid, paid, cancelled, refunded, or expired</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">email</code></td>
                                            <td>Filter by buyer email (exact match)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">event_date</code></td>
                                            <td>Filter by event date (Y-m-d)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/sales?status=paid&subdomain=my-venue"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span></code></pre>
                            </div>
                        </section>

                        <!-- Show Sale -->
                        <section id="show-sale" class="doc-section">
                            <h2 class="doc-heading">Show Sale</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">GET</span>
                                <code class="doc-inline-code">/api/sales/{id}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Returns a single sale by encoded ID, including ticket details.</p>
                        </section>

                        <!-- Create Sale -->
                        <section id="create-sale" class="doc-section">
                            <h2 class="doc-heading">Create Sale</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-green-600 text-white px-2 py-1 rounded text-sm font-medium">POST</span>
                                <code class="doc-inline-code">/api/sales</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Create a new sale manually for an event. Sales are created as unpaid (free tickets are auto-marked as paid).</p>

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
                                            <td>Object mapping ticket identifiers to quantities. Keys can be encoded ticket IDs or ticket type names.</td>
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
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X POST <span class="code-string">"{{ config('app.url') }}/api/sales"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
     -H <span class="code-string">"Content-Type: application/json"</span> \
     -d <span class="code-string">'{
         "event_id": "456",
         "name": "John Doe",
         "email": "john@example.com",
         "tickets": {"General Admission": 2}
     }'</span></code></pre>
                            </div>
                        </section>

                        <!-- Update Sale -->
                        <section id="update-sale" class="doc-section">
                            <h2 class="doc-heading">Update Sale Status</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-yellow-600 text-white px-2 py-1 rounded text-sm font-medium">PUT</span>
                                <code class="doc-inline-code">/api/sales/{id}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Perform a status action on a sale. Available actions depend on the current status.</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Action</th>
                                            <th>From Status</th>
                                            <th>To Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">mark_paid</code></td>
                                            <td>unpaid</td>
                                            <td>paid</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">refund</code></td>
                                            <td>paid</td>
                                            <td>refunded</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">cancel</code></td>
                                            <td>unpaid, paid</td>
                                            <td>cancelled</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>cURL</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">curl</span> -X PUT <span class="code-string">"{{ config('app.url') }}/api/sales/abc123"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
     -H <span class="code-string">"Content-Type: application/json"</span> \
     -d <span class="code-string">'{"action": "mark_paid"}'</span></code></pre>
                            </div>
                        </section>

                        <!-- Delete Sale -->
                        <section id="delete-sale" class="doc-section">
                            <h2 class="doc-heading">Delete Sale</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-red-600 text-white px-2 py-1 rounded text-sm font-medium">DELETE</span>
                                <code class="doc-inline-code">/api/sales/{id}</code>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Soft-delete a sale. The sale will no longer appear in listings.</p>
                        </section>

                        <!-- Error Handling -->
                        <section id="error-handling" class="doc-section">
                            <h2 class="doc-heading">Error Handling</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The API uses standard HTTP status codes and returns error messages in JSON format.</p>

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
                                            <td>Forbidden (insufficient permissions or Pro required)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="text-red-400 font-semibold">404</span></td>
                                            <td>Not found</td>
                                        </tr>
                                        <tr>
                                            <td><span class="text-red-400 font-semibold">422</span></td>
                                            <td>Validation error (includes field-level errors)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="text-red-400 font-semibold">429</span></td>
                                            <td>Rate limit exceeded</td>
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
                                    <span>Validation Error Response</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code>{
    <span class="code-string">"error"</span>: <span class="code-string">"Validation failed"</span>,
    <span class="code-string">"errors"</span>: {
        <span class="code-string">"name"</span>: [<span class="code-string">"The name field is required."</span>],
        <span class="code-string">"starts_at"</span>: [<span class="code-string">"The starts at must match the format Y-m-d H:i:s."</span>]
    }
}</code></pre>
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
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
