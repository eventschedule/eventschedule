<x-app-admin-layout>
    <div>
        <script {!! nonce_attr() !!}>
            // Copy code buttons - event delegation
            document.addEventListener('click', function(e) {
                var button = e.target.closest('.copy-btn');
                if (!button) return;

                var container = button.closest('.bg-gray-800, .bg-gray-950');

                if (!container) {
                    console.error('Could not find container for code block');
                    button.textContent = 'Copy failed!';
                    setTimeout(function() {
                        button.textContent = 'Copy';
                    }, 2000);
                    return;
                }

                var codeBlock = container.querySelector('pre');

                if (!codeBlock) {
                    console.error('Could not find code block to copy');
                    button.textContent = 'Copy failed!';
                    setTimeout(function() {
                        button.textContent = 'Copy';
                    }, 2000);
                    return;
                }

                var code = codeBlock.textContent;

                navigator.clipboard.writeText(code).then(function() {
                    var originalText = button.textContent;
                    button.textContent = 'Copied!';
                    setTimeout(function() {
                        button.textContent = originalText;
                    }, 2000);
                }).catch(function(err) {
                    console.error('Failed to copy: ', err);
                    button.textContent = 'Copy failed!';
                    setTimeout(function() {
                        button.textContent = 'Copy';
                    }, 2000);
                });
            });

            // Toggle cURL example buttons - event delegation
            document.addEventListener('click', function(e) {
                var button = e.target.closest('.toggle-curl-btn');
                if (!button) return;

                var content = button.nextElementSibling;
                var arrow = button.querySelector('svg');

                content.classList.toggle('hidden');
                arrow.style.transform = content.classList.contains('hidden') ? '' : 'rotate(-180deg)';

                var text = button.innerHTML;
                button.innerHTML = text.replace(
                    content.classList.contains('hidden') ? 'Hide' : 'Show',
                    content.classList.contains('hidden') ? 'Show' : 'Hide'
                );
            });
        </script>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="border-b border-gray-200 pb-5">
                        <h1 class="text-4xl font-bold">API Documentation</h1>
                        <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">
                            Event Schedule provides a REST API that allows you to programmatically manage schedules and events.
                        </p>
                    </div>

                    <!-- Authentication Section -->
                    <div class="mt-8">
                        <h2 class="text-2xl font-semibold mb-4">Authentication</h2>
                        <div class="lg:grid lg:grid-cols-2 lg:gap-8">
                            <div class="prose dark:prose-invert">
                                <p>All API requests must include your API key in the <code>X-API-Key</code> header.</p>
                                <button class="toggle-curl-btn mt-2 text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center">
                                    <svg class="w-4 h-4 mr-1 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Show cURL example
                                </button>
                                <div class="hidden mt-2">
                                    <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                        <div class="flex items-center justify-between">
                                            <span>cURL</span>
                                            <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                        </div>
                                        <pre class="mt-2 overflow-x-auto"><code>curl -X GET "{{ config('app.url') }}/api/schedules" \
     -H "X-API-Key: your_api_key_here"</code></pre>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 lg:mt-0">
                                <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                    <div class="flex items-center justify-between">
                                        <span>Request Headers</span>
                                        <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                    </div>
                                    <pre class="mt-2 overflow-x-auto"><code>X-API-Key: your_api_key_here</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Response Format -->
                    <div class="mt-8">
                        <h2 class="text-2xl font-semibold mb-4">Response Format</h2>
                        <div class="lg:grid lg:grid-cols-2 lg:gap-8">
                            <div class="prose dark:prose-invert">
                                <p>All API responses follow a consistent format with two main properties:</p>
                                <ul class="mt-2">
                                    <li><code>data</code>: Contains the response payload (array for lists, object for single items)</li>
                                    <li><code>meta</code>: Contains metadata about the response, including pagination information</li>
                                </ul>
                            </div>
                            <div class="mt-4 lg:mt-0">
                                <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                    <div class="flex items-center justify-between">
                                        <span>Example Response</span>
                                        <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                    </div>
                                    <pre class="mt-2 overflow-x-auto"><code>{
    "data": [...],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 100,
        "to": 100,
        "total": 450,
        "path": "/api/schedules"
    }
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8">
                        <h2 class="text-2xl font-semibold mb-4">Pagination</h2>
                        <div class="lg:grid lg:grid-cols-2 lg:gap-8">
                            <div class="prose dark:prose-invert">
                                <p>List endpoints support pagination through query parameters:</p>
                                <ul class="mt-2">
                                    <li><code>per_page</code>: Number of items per page (default: 100, max: 1000)</li>
                                    <li><code>page</code>: Page number to retrieve</li>
                                </ul>
                                <p class="mt-4">Example request:</p>
                                <code>/api/schedules?page=2&per_page=50</code>
                            </div>
                            <div class="mt-4 lg:mt-0">
                                <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                    <div class="flex items-center justify-between">
                                        <span>Pagination Metadata</span>
                                        <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                    </div>
                                    <pre class="mt-2 overflow-x-auto"><code>"meta": {
    "current_page": 2,
    "from": 51,
    "last_page": 5,
    "per_page": 50,
    "to": 100,
    "total": 250,
    "path": "/api/schedules"
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- List Schedules Endpoint -->
                    <div class="mt-12">
                        <h2 class="text-2xl font-semibold mb-6">Endpoints</h2>
                        <div class="border dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="bg-gray-100 dark:bg-gray-900 px-4 py-2 border-b dark:border-gray-700">
                                <h3 class="text-xl font-medium">List Schedules</h3>
                            </div>
                            <div class="lg:grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x dark:divide-gray-700">
                                <div class="p-4 prose dark:prose-invert">
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm">GET</span>
                                        <code class="text-sm">/api/schedules</code>
                                    </div>
                                    <p class="mt-4">Returns a paginated list of all schedules you have access to.</p>
                                    <button class="toggle-curl-btn mt-2 text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center">
                                        <svg class="w-4 h-4 mr-1 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Show cURL example
                                    </button>
                                    <div class="hidden mt-2">
                                        <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                            <div class="flex items-center justify-between">
                                                <span>cURL</span>
                                                <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                            </div>
                                            <pre class="mt-2 overflow-x-auto"><code>curl -X GET "{{ config('app.url') }}/api/schedules?page=1&per_page=100" \
     -H "X-API-Key: your_api_key_here"</code></pre>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                        <div class="flex items-center justify-between">
                                            <span>Response</span>
                                            <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                        </div>
                                        <pre class="mt-2 overflow-x-auto"><code>{
    "data": [
        {
            "id": "123",
            "url": "{{ config('app.url') }}/venue-name",
            "type": "venue",
            "name": "My Venue",
            "email": "venue@example.com",
            "website": "https://example.com",
            "description": "Venue description",
            "address1": "123 Main St",
            "city": "New York",
            "state": "NY",
            "postal_code": "10001",
            "country_code": "US"
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "per_page": 100,
        "to": 1,
        "total": 1,
        "path": "/api/schedules"
    }
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- List Events Endpoint -->
                    <div class="mt-8">
                        <div class="border dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="bg-gray-100 dark:bg-gray-900 px-4 py-2 border-b dark:border-gray-700">
                                <h3 class="text-xl font-medium">List Events</h3>
                            </div>
                            <div class="lg:grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x dark:divide-gray-700">
                                <div class="p-4 prose dark:prose-invert">
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm">GET</span>
                                        <code class="text-sm">/api/events</code>
                                    </div>
                                    <p class="mt-4">Returns a paginated list of all events.</p>
                                    <button class="toggle-curl-btn mt-2 text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center">
                                        <svg class="w-4 h-4 mr-1 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Show cURL example
                                    </button>
                                    <div class="hidden mt-2">
                                        <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                            <div class="flex items-center justify-between">
                                                <span>cURL</span>
                                                <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                            </div>
                                            <pre class="mt-2 overflow-x-auto"><code>curl -X GET "{{ config('app.url') }}/api/events" \
     -H "X-API-Key: your_api_key_here"</code></pre>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                        <div class="flex items-center justify-between">
                                            <span>Response</span>
                                            <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                        </div>
                                        <pre class="mt-2 overflow-x-auto"><code>{
    "data": [
        {
            "id": "456",
            "url": "{{ config('app.url') }}/venue-name/event-slug",
            "name": "Event Name",
            "short_description": "Brief event summary",
            "description": "Event description",
            "starts_at": "{{ now()->format('Y-m-d') }} 19:00:00",
            "duration": 3,
            "venue_id": "123"
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "per_page": 100,
        "to": 1,
        "total": 1,
        "path": "/api/schedules/123/events"
    }
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Create Event Endpoint -->
                    <div class="mt-8">
                        <div class="border dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="bg-gray-100 dark:bg-gray-900 px-4 py-2 border-b dark:border-gray-700">
                                <h3 class="text-xl font-medium">Create Event</h3>
                            </div>
                            <div class="lg:grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x dark:divide-gray-700">
                                <div class="p-4 prose dark:prose-invert">
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-green-600 text-white px-2 py-1 rounded text-sm">POST</span>
                                        <code class="text-sm">/api/events/{subdomain}</code>
                                    </div>
                                    <p class="mt-4">Create a new event using either JSON data or a flyer image.</p>
                                    
                                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Schedule & Category Support</h4>
                                        <p class="text-sm text-blue-800 dark:text-blue-200">
                                            You can specify schedules and categories by ID or name:
                                        </p>
                                        <ul class="text-sm text-blue-800 dark:text-blue-200 mt-2 space-y-1">
                                            <li><strong>schedule</strong>: Assigns event to a specific sub-schedule</li>
                                            <li><strong>category</strong> or <strong>category_id</strong>: Sets event category</li>
                                        </ul>
                                        <p class="text-sm text-blue-800 dark:text-blue-200 mt-2">
                                            When using names, the API will automatically find matching schedules by slug and categories by name.
                                        </p>
                                    </div>
                                    
                                    <button class="toggle-curl-btn mt-2 text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center">
                                        <svg class="w-4 h-4 mr-1 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Show cURL example (JSON)
                                    </button>
                                    <div class="hidden mt-2">
                                        <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                            <div class="flex items-center justify-between">
                                                <span>cURL</span>
                                                <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                            </div>
                                            <pre class="mt-2 overflow-x-auto"><code>curl -X POST "{{ config('app.url') }}/api/events/{subdomain}" \
     -H "X-API-Key: your_api_key_here" \
     -H "X-Requested-With: XMLHttpRequest" \
     -H "Content-Type: application/json" \
     -d '{
         "name": "Event Name",
         "short_description": "Brief event summary",
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
     }'</code></pre>
                                        </div>
                                    </div>
                                    
                                    <button class="toggle-curl-btn mt-4 text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center">
                                        <svg class="w-4 h-4 mr-1 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Show cURL example (Flyer Image)
                                    </button>
                                    <div class="hidden mt-2">
                                        <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                            <div class="flex items-center justify-between">
                                                <span>cURL</span>
                                                <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                            </div>
                                            <pre class="mt-2 overflow-x-auto"><code>curl -X POST "{{ config('app.url') }}/api/events/{subdomain}" \
    -H "X-API-Key: your_api_key_here" \
    -H "X-Requested-With: XMLHttpRequest" \
    -F "flyer_image=@/path/to/your/flyer.jpg" \
    -F "name=Event Name" \
    -F "short_description=Brief event summary" \
    -F "description=Event description" \
    -F "starts_at=2025-04-14 19:00:00" \
    -F "duration=2" \
    -F "venue_name=Carnegie Hall" \
    -F "venue_address1=111 Main st" \
    -F "schedule=main-schedule" \
    -F "category=music" \
    -F "members[0][name]=John Doe" \
    -F "members[0][email]=john@example.com" \
    -F "members[0][youtube_url]=https://www.youtube.com/watch?v=RbXXUHABGRU"</code></pre>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                        <div class="flex items-center justify-between">
                                            <span>Response</span>
                                            <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                        </div>
                                        <pre class="mt-2 overflow-x-auto"><code>{
    "data": {
        "event_url": "https://example.com",
        "name": "Event Name",
        "short_description": "Brief event summary",
        "description": "Event description",
        "starts_at": "{{ now()->format('Y-m-d') }} 19:00:00",
        "duration": 2,
        "venue_name": "Carnegie Hall",
        "venue_address1": "123 Main St",
        "schedule": "main-schedule",
        "category": "music",
        "members": {
            "123": {
                "name": "John Doe",
                "email": "john@example.com"
            }
        }
    },
    "meta": {
        "message": "Event created successfully"
    }
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- List Sales Endpoint -->
                    <div class="mt-8">
                        <div class="border dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="bg-gray-100 dark:bg-gray-900 px-4 py-2 border-b dark:border-gray-700">
                                <h3 class="text-xl font-medium">List Sales</h3>
                            </div>
                            <div class="lg:grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x dark:divide-gray-700">
                                <div class="p-4 prose dark:prose-invert">
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm">GET</span>
                                        <code class="text-sm">/api/sales</code>
                                    </div>
                                    <p class="mt-4">Returns a paginated list of sales for events you own or administer.</p>

                                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Filters</h4>
                                        <ul class="text-sm text-blue-800 dark:text-blue-200 mt-2 space-y-1">
                                            <li><strong>event_id</strong>: Filter by event (encoded ID)</li>
                                            <li><strong>subdomain</strong>: Filter by schedule subdomain</li>
                                            <li><strong>status</strong>: unpaid, paid, cancelled, refunded, or expired</li>
                                            <li><strong>email</strong>: Filter by buyer email</li>
                                            <li><strong>event_date</strong>: Filter by event date (Y-m-d)</li>
                                        </ul>
                                    </div>

                                    <button class="toggle-curl-btn mt-2 text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center">
                                        <svg class="w-4 h-4 mr-1 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Show cURL example
                                    </button>
                                    <div class="hidden mt-2">
                                        <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                            <div class="flex items-center justify-between">
                                                <span>cURL</span>
                                                <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                            </div>
                                            <pre class="mt-2 overflow-x-auto"><code>curl -X GET "{{ config('app.url') }}/api/sales?status=paid&subdomain=my-venue" \
     -H "X-API-Key: your_api_key_here"</code></pre>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                        <div class="flex items-center justify-between">
                                            <span>Response</span>
                                            <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                        </div>
                                        <pre class="mt-2 overflow-x-auto"><code>{
    "data": [
        {
            "id": "789",
            "event_id": "456",
            "event_name": "Jazz Night",
            "subdomain": "my-venue",
            "name": "John Doe",
            "email": "john@example.com",
            "status": "paid",
            "payment_amount": 50.00
        }
    ],
    "meta": {
        "current_page": 1,
        "total": 1
    }
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Create Sale Endpoint -->
                    <div class="mt-8">
                        <div class="border dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="bg-gray-100 dark:bg-gray-900 px-4 py-2 border-b dark:border-gray-700">
                                <h3 class="text-xl font-medium">Create Sale</h3>
                            </div>
                            <div class="lg:grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x dark:divide-gray-700">
                                <div class="p-4 prose dark:prose-invert">
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-green-600 text-white px-2 py-1 rounded text-sm">POST</span>
                                        <code class="text-sm">/api/sales</code>
                                    </div>
                                    <p class="mt-4">Create a new sale manually for an event. Sales are created as unpaid (free tickets are auto-marked as paid).</p>

                                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Request Body</h4>
                                        <ul class="text-sm text-blue-800 dark:text-blue-200 mt-2 space-y-1">
                                            <li><strong>event_id</strong> (required): Encoded event ID</li>
                                            <li><strong>name</strong> (required): Customer name</li>
                                            <li><strong>email</strong> (required): Customer email</li>
                                            <li><strong>tickets</strong> (required): Object mapping ticket IDs or type names to quantities, e.g., <code>{"General Admission": 2}</code></li>
                                            <li><strong>event_date</strong> (optional): Event date in Y-m-d format (defaults to event start date)</li>
                                        </ul>
                                    </div>

                                    <button class="toggle-curl-btn mt-2 text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center">
                                        <svg class="w-4 h-4 mr-1 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Show cURL example
                                    </button>
                                    <div class="hidden mt-2">
                                        <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                            <div class="flex items-center justify-between">
                                                <span>cURL</span>
                                                <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                            </div>
                                            <pre class="mt-2 overflow-x-auto"><code>curl -X POST "{{ config('app.url') }}/api/sales" \
     -H "X-API-Key: your_api_key_here" \
     -H "Content-Type: application/json" \
     -d '{
         "event_id": "456",
         "name": "John Doe",
         "email": "john@example.com",
         "tickets": {
             "General Admission": 2,
             "VIP": 1
         },
         "event_date": "{{ now()->format('Y-m-d') }}"
     }'</code></pre>
                                        </div>
                                    </div>

                                    <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                        <h4 class="font-medium text-yellow-900 dark:text-yellow-100 mb-2">Requirements</h4>
                                        <ul class="text-sm text-yellow-800 dark:text-yellow-200 mt-2 space-y-1">
                                            <li>Event must belong to the authenticated user</li>
                                            <li>Event must have tickets enabled and be a Pro account</li>
                                            <li>All ticket IDs must exist and belong to the event</li>
                                            <li>Ticket quantities must not exceed available tickets</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                        <div class="flex items-center justify-between">
                                            <span>Response</span>
                                            <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                        </div>
                                        <pre class="mt-2 overflow-x-auto"><code>{
    "data": {
        "id": "789",
        "event_id": "456",
        "event_name": "Jazz Night",
        "subdomain": "my-venue",
        "name": "John Doe",
        "email": "john@example.com",
        "event_date": "{{ now()->format('Y-m-d') }}",
        "status": "unpaid",
        "payment_method": "manual",
        "payment_amount": 50.00,
        "transaction_reference": null,
        "secret": "abc123def456...",
        "created_at": "{{ now()->toISOString() }}",
        "updated_at": "{{ now()->toISOString() }}",
        "tickets": [
            {
                "ticket_id": "ticket_id_1",
                "quantity": 2,
                "price": 20.00,
                "type": "General Admission"
            },
            {
                "ticket_id": "ticket_id_2",
                "quantity": 1,
                "price": 10.00,
                "type": "VIP"
            }
        ],
        "total_quantity": 3
    },
    "meta": {
        "message": "Sale created successfully"
    }
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Sale Endpoint -->
                    <div class="mt-8">
                        <div class="border dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="bg-gray-100 dark:bg-gray-900 px-4 py-2 border-b dark:border-gray-700">
                                <h3 class="text-xl font-medium">Update Sale Status</h3>
                            </div>
                            <div class="lg:grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x dark:divide-gray-700">
                                <div class="p-4 prose dark:prose-invert">
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-yellow-600 text-white px-2 py-1 rounded text-sm">PUT</span>
                                        <code class="text-sm">/api/sales/{id}</code>
                                    </div>
                                    <p class="mt-4">Perform a status action on a sale.</p>

                                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Available Actions</h4>
                                        <ul class="text-sm text-blue-800 dark:text-blue-200 mt-2 space-y-1">
                                            <li><strong>mark_paid</strong>: unpaid -> paid</li>
                                            <li><strong>refund</strong>: paid -> refunded</li>
                                            <li><strong>cancel</strong>: unpaid or paid -> cancelled</li>
                                        </ul>
                                    </div>

                                    <button class="toggle-curl-btn mt-2 text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center">
                                        <svg class="w-4 h-4 mr-1 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Show cURL example
                                    </button>
                                    <div class="hidden mt-2">
                                        <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                            <div class="flex items-center justify-between">
                                                <span>cURL</span>
                                                <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                            </div>
                                            <pre class="mt-2 overflow-x-auto"><code>curl -X PUT "{{ config('app.url') }}/api/sales/789" \
     -H "X-API-Key: your_api_key_here" \
     -H "Content-Type: application/json" \
     -d '{"action": "mark_paid"}'</code></pre>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                        <div class="flex items-center justify-between">
                                            <span>Response</span>
                                            <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                        </div>
                                        <pre class="mt-2 overflow-x-auto"><code>{
    "data": {
        "id": "789",
        "event_id": "456",
        "event_name": "Jazz Night",
        "status": "paid",
        "transaction_reference": "Manual payment (API)"
    },
    "meta": {
        "message": "Sale updated successfully"
    }
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Sale Endpoint -->
                    <div class="mt-8">
                        <div class="border dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="bg-gray-100 dark:bg-gray-900 px-4 py-2 border-b dark:border-gray-700">
                                <h3 class="text-xl font-medium">Delete Sale</h3>
                            </div>
                            <div class="lg:grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x dark:divide-gray-700">
                                <div class="p-4 prose dark:prose-invert">
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-red-600 text-white px-2 py-1 rounded text-sm">DELETE</span>
                                        <code class="text-sm">/api/sales/{id}</code>
                                    </div>
                                    <p class="mt-4">Soft-delete a sale. The sale will no longer appear in listings.</p>

                                    <button class="toggle-curl-btn mt-2 text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center">
                                        <svg class="w-4 h-4 mr-1 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Show cURL example
                                    </button>
                                    <div class="hidden mt-2">
                                        <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                            <div class="flex items-center justify-between">
                                                <span>cURL</span>
                                                <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                            </div>
                                            <pre class="mt-2 overflow-x-auto"><code>curl -X DELETE "{{ config('app.url') }}/api/sales/789" \
     -H "X-API-Key: your_api_key_here"</code></pre>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                        <div class="flex items-center justify-between">
                                            <span>Response</span>
                                            <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                        </div>
                                        <pre class="mt-2 overflow-x-auto"><code>{
    "data": {
        "message": "Sale deleted successfully"
    }
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Error Handling -->
                    <div class="mt-12">
                        <h2 class="text-2xl font-semibold mb-4">Error Handling</h2>
                        <div class="lg:grid lg:grid-cols-2 lg:gap-8">
                            <div class="prose dark:prose-invert">
                                <p>The API uses standard HTTP status codes and returns error messages in JSON format.</p>
                                <div class="mt-4">
                                    <h4 class="font-medium">Common Status Codes</h4>
                                    <ul class="mt-2 space-y-1">
                                        <li class="flex items-center">
                                            <span class="w-12 text-green-600">200</span>
                                            <span>Success</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="w-12 text-green-600">201</span>
                                            <span>Created</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="w-12 text-red-600">401</span>
                                            <span>Unauthorized (invalid or missing API key)</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="w-12 text-red-600">403</span>
                                            <span>Forbidden (insufficient permissions)</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="w-12 text-red-600">404</span>
                                            <span>Not found</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="w-12 text-red-600">422</span>
                                            <span>Validation error</span>
                                        </li>
                                        <li class="flex items-center">
                                            <span class="w-12 text-red-600">500</span>
                                            <span>Server error</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mt-4 lg:mt-0">
                                <div class="bg-gray-800 dark:bg-gray-950 rounded-lg p-4 text-white font-mono text-sm">
                                    <div class="flex items-center justify-between">
                                        <span>Error Response</span>
                                        <button class="copy-btn text-xs text-gray-400 hover:text-white">Copy</button>
                                    </div>
                                    <pre class="mt-2"><code>{
    "data": null,
    "meta": {
        "error": "Error message here"
    }
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout> 