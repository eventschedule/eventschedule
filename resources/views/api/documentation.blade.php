<x-app-admin-layout>
    <div class="py-12">
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
                            </div>
                            <div class="mt-4 lg:mt-0">
                                <div class="bg-gray-800 rounded-lg p-4 text-white font-mono text-sm">
                                    <div class="flex items-center justify-between">
                                        <span>Request Headers</span>
                                        <button onclick="copyCode(this)" class="text-xs text-gray-400 hover:text-white">Copy</button>
                                    </div>
                                    <pre class="mt-2"><code>X-API-Key: your_api_key_here</code></pre>
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
                                <div class="bg-gray-800 rounded-lg p-4 text-white font-mono text-sm">
                                    <div class="flex items-center justify-between">
                                        <span>Example Response</span>
                                        <button onclick="copyCode(this)" class="text-xs text-gray-400 hover:text-white">Copy</button>
                                    </div>
                                    <pre class="mt-2"><code>{
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
                                <div class="bg-gray-800 rounded-lg p-4 text-white font-mono text-sm">
                                    <div class="flex items-center justify-between">
                                        <span>Pagination Metadata</span>
                                        <button onclick="copyCode(this)" class="text-xs text-gray-400 hover:text-white">Copy</button>
                                    </div>
                                    <pre class="mt-2"><code>"meta": {
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
                                </div>
                                <div class="p-4">
                                    <div class="bg-gray-800 rounded-lg p-4 text-white font-mono text-sm">
                                        <div class="flex items-center justify-between">
                                            <span>Response</span>
                                            <button onclick="copyCode(this)" class="text-xs text-gray-400 hover:text-white">Copy</button>
                                        </div>
                                        <pre class="mt-2 overflow-x-auto"><code>{
    "data": [
        {
            "id": "123",
            "name": "My Schedule",
            "type": "schedule",
            "description": "Schedule description"
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
                                <h3 class="text-xl font-medium">List Events for a Schedule</h3>
                            </div>
                            <div class="lg:grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x dark:divide-gray-700">
                                <div class="p-4 prose dark:prose-invert">
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm">GET</span>
                                        <code class="text-sm">/api/schedules/{schedule_id}/events</code>
                                    </div>
                                    <p class="mt-4">Returns a paginated list of all events for the specified schedule.</p>
                                </div>
                                <div class="p-4">
                                    <div class="bg-gray-800 rounded-lg p-4 text-white font-mono text-sm">
                                        <div class="flex items-center justify-between">
                                            <span>Response</span>
                                            <button onclick="copyCode(this)" class="text-xs text-gray-400 hover:text-white">Copy</button>
                                        </div>
                                        <pre class="mt-2 overflow-x-auto"><code>{
    "data": [
        {
            "id": "456",
            "title": "Event Name",
            "description": "Event description",
            "start_time": "2024-04-01T19:00:00Z",
            "end_time": "2024-04-01T22:00:00Z",
            "location": "Event location"
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
                                        <code class="text-sm">/api/schedules/{schedule_id}/events</code>
                                    </div>
                                    <p class="mt-4">Create a new event using either JSON data or a flyer image.</p>
                                    
                                    <h4 class="font-medium mt-4">Option 1: JSON Request</h4>
                                    <div class="bg-gray-800 rounded-lg p-4 text-white font-mono text-sm mt-2">
                                        <div class="flex items-center justify-between">
                                            <span>Request Body</span>
                                            <button onclick="copyCode(this)" class="text-xs text-gray-400 hover:text-white">Copy</button>
                                        </div>
                                        <pre class="mt-2"><code>{
    "name": "Event Name",
    "description": "Event description",
    "starts_at": "2024-04-01T19:00:00Z",
    "ends_at": "2024-04-01T22:00:00Z",
    "location": "Event location"
}</code></pre>
                                    </div>

                                    <h4 class="font-medium mt-4">Option 2: Flyer Upload</h4>
                                    <ul class="mt-2 list-disc list-inside">
                                        <li>Send as <code>multipart/form-data</code></li>
                                        <li>Include flyer image in <code>flyer</code> field</li>
                                        <li>Event details will be extracted automatically</li>
                                    </ul>
                                </div>
                                <div class="p-4">
                                    <div class="bg-gray-800 rounded-lg p-4 text-white font-mono text-sm">
                                        <div class="flex items-center justify-between">
                                            <span>Response</span>
                                            <button onclick="copyCode(this)" class="text-xs text-gray-400 hover:text-white">Copy</button>
                                        </div>
                                        <pre class="mt-2 overflow-x-auto"><code>{
    "data": {
        "id": "456",
        "title": "Event Name",
        "description": "Event description",
        "start_time": "2024-04-01T19:00:00Z",
        "end_time": "2024-04-01T22:00:00Z",
        "location": "Event location"
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
                                <div class="bg-gray-800 rounded-lg p-4 text-white font-mono text-sm">
                                    <div class="flex items-center justify-between">
                                        <span>Error Response</span>
                                        <button onclick="copyCode(this)" class="text-xs text-gray-400 hover:text-white">Copy</button>
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

    @push('scripts')
    <script>
        function copyCode(button) {
            const pre = button.closest('div').querySelector('pre');
            const code = pre.textContent;
            
            navigator.clipboard.writeText(code).then(() => {
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            });
        }
    </script>
    @endpush
</x-app-admin-layout> 