<x-marketing-layout>
    <x-slot name="title">Webhooks - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Webhooks</x-slot>
    <x-slot name="description">Receive real-time POST notifications for sales, events, and check-ins. HMAC-signed payloads, configurable event types, and delivery logs.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Webhooks - Event Schedule",
        "description": "Receive real-time POST notifications for sales, events, and check-ins via webhooks.",
        "author": { "@type": "Organization", "name": "Event Schedule" },
        "publisher": { "@type": "Organization", "name": "Event Schedule", "logo": { "@type": "ImageObject", "url": "{{ config('app.url') }}/images/light_logo.png", "width": 712, "height": 140 } },
        "mainEntityOfPage": { "@type": "WebPage", "@id": "{{ url()->current() }}" },
        "datePublished": "2026-03-01",
        "dateModified": "2026-03-01"
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
            <x-docs-breadcrumb currentTitle="Webhooks" section="developer" sectionTitle="Developer" :sectionRoute="'marketing.docs.developer.api'" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Webhooks</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Receive real-time HTTP POST notifications when events happen in your schedules.
            </p>
            <div class="mt-4 inline-flex items-center px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-medium">Pro plan required</div>
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
                        <a href="#overview" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Overview</a>
                        <a href="#setup" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Setup</a>
                        <a href="#event-types" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Event Types</a>
                        <a href="#payload" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Payload Format</a>
                        <a href="#headers" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Request Headers</a>
                        <a href="#verification" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Signature Verification</a>
                        <a href="#best-practices" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Best Practices</a>
                        <a href="#testing" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Testing</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section class="doc-section" id="overview">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Overview
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Webhooks let you receive automatic POST notifications to your server when key events occur, such as ticket sales, event changes, or check-ins. Instead of polling the API, your application is notified in real time.
                            </p>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Each webhook delivery includes an HMAC-SHA256 signature so you can verify the payload came from Event Schedule. Delivery logs are available in your account settings for debugging.
                            </p>
                        </section>

                        <!-- Setup -->
                        <section class="doc-section" id="setup">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
                                </svg>
                                Setup
                            </h2>
                            <ol class="doc-list doc-list-numbered">
                                <li>Go to <strong>Settings</strong> in the admin panel and select the <strong>Webhooks</strong> section.</li>
                                <li>Enter your endpoint URL (must be HTTPS in production).</li>
                                <li>Select which event types you want to receive.</li>
                                <li>Click <strong>Add Webhook</strong>. Your signing secret will be displayed once. Copy and store it securely.</li>
                                <li>Use the <strong>Test</strong> button to send a test ping and verify your endpoint responds with a 2xx status.</li>
                            </ol>
                        </section>

                        <!-- Event Types -->
                        <section class="doc-section" id="event-types">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                Event Types
                            </h2>
                            <div class="overflow-x-auto">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Event</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td class="font-mono text-sm">sale.created</td><td>A new ticket sale is created (status: unpaid)</td></tr>
                                        <tr><td class="font-mono text-sm">sale.paid</td><td>A sale is confirmed as paid (Stripe, Invoice Ninja, manual, or free)</td></tr>
                                        <tr><td class="font-mono text-sm">sale.refunded</td><td>A sale is refunded</td></tr>
                                        <tr><td class="font-mono text-sm">sale.cancelled</td><td>A sale is cancelled</td></tr>
                                        <tr><td class="font-mono text-sm">event.created</td><td>A new event is created</td></tr>
                                        <tr><td class="font-mono text-sm">event.updated</td><td>An event is updated</td></tr>
                                        <tr><td class="font-mono text-sm">event.deleted</td><td>An event is deleted</td></tr>
                                        <tr><td class="font-mono text-sm">ticket.scanned</td><td>A ticket QR code is scanned at check-in</td></tr>
                                        <tr><td class="font-mono text-sm">feedback.submitted</td><td>An attendee submits feedback for an event</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Payload Format -->
                        <section class="doc-section" id="payload">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                Payload Format
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">All webhook payloads are JSON with this structure:</p>
                            <div class="doc-code-block">
                                <pre><code>{
  "event": "sale.paid",
  "timestamp": "2026-03-01T12:00:00+00:00",
  "data": {
    "id": "abc123",
    "event_id": "def456",
    "event_name": "Summer Concert",
    "name": "Jane Doe",
    "email": "jane@example.com",
    "status": "paid",
    "payment_amount": 25.00,
    "tickets": [
      { "ticket_id": "ghi789", "quantity": 2, "price": 12.50, "type": "General" }
    ]
  }
}</code></pre>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mt-4">
                                The <code class="doc-inline-code">data</code> object matches the corresponding API response format. Sale webhooks include the same fields as the <x-link href="{{ route('marketing.docs.developer.api') }}">Sales API</x-link>, and event webhooks match the Events API.
                            </p>
                        </section>

                        <!-- Headers -->
                        <section class="doc-section" id="headers">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                                </svg>
                                Request Headers
                            </h2>
                            <div class="overflow-x-auto">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Header</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td class="font-mono text-sm">X-Webhook-Signature</td><td>HMAC-SHA256 signature: <code class="doc-inline-code">sha256=&lt;hex&gt;</code></td></tr>
                                        <tr><td class="font-mono text-sm">X-Webhook-Event</td><td>The event type (e.g. <code class="doc-inline-code">sale.paid</code>)</td></tr>
                                        <tr><td class="font-mono text-sm">X-Webhook-Timestamp</td><td>ISO 8601 timestamp of when the webhook was sent</td></tr>
                                        <tr><td class="font-mono text-sm">Content-Type</td><td><code class="doc-inline-code">application/json</code></td></tr>
                                        <tr><td class="font-mono text-sm">User-Agent</td><td><code class="doc-inline-code">EventSchedule-Webhook/1.0</code></td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Signature Verification -->
                        <section class="doc-section" id="verification">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                </svg>
                                Signature Verification
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Every webhook includes an <code class="doc-inline-code">X-Webhook-Signature</code> header containing an HMAC-SHA256 hash of the raw request body, signed with your webhook secret. Always verify this signature before processing the payload.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">PHP</h3>
                            <div class="doc-code-block">
                                <pre><code>$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

$expected = 'sha256=' . hash_hmac('sha256', $payload, $webhookSecret);

if (!hash_equals($expected, $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}

$data = json_decode($payload, true);</code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">Node.js</h3>
                            <div class="doc-code-block">
                                <pre><code>const crypto = require('crypto');

function verifyWebhook(body, signature, secret) {
  const expected = 'sha256=' +
    crypto.createHmac('sha256', secret).update(body).digest('hex');
  return crypto.timingSafeEqual(
    Buffer.from(expected), Buffer.from(signature)
  );
}</code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">Python</h3>
                            <div class="doc-code-block">
                                <pre><code>import hmac, hashlib

def verify_webhook(body: bytes, signature: str, secret: str) -> bool:
    expected = 'sha256=' + hmac.new(
        secret.encode(), body, hashlib.sha256
    ).hexdigest()
    return hmac.compare_digest(expected, signature)</code></pre>
                            </div>
                        </section>

                        <!-- Best Practices -->
                        <section class="doc-section" id="best-practices">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                                </svg>
                                Best Practices
                            </h2>
                            <ul class="doc-list">
                                <li><strong>Respond quickly.</strong> Return a 2xx status within 5 seconds. Do heavy processing asynchronously after acknowledging receipt.</li>
                                <li><strong>Verify signatures.</strong> Always validate the <code class="doc-inline-code">X-Webhook-Signature</code> header before processing any webhook payload.</li>
                                <li><strong>Handle duplicates.</strong> Use the <code class="doc-inline-code">data.id</code> field as an idempotency key. In rare cases, the same event may be delivered more than once.</li>
                                <li><strong>Use HTTPS.</strong> Always use an HTTPS endpoint to protect webhook payloads in transit.</li>
                                <li><strong>Monitor deliveries.</strong> Check the delivery log in your Webhook settings to debug failed deliveries and verify payloads.</li>
                            </ul>
                        </section>

                        <!-- Test Ping -->
                        <section class="doc-section" id="testing">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                </svg>
                                Testing
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Use the <strong>Test</strong> button in your webhook settings to send a test payload. The test event uses the type <code class="doc-inline-code">webhook.test</code> with an empty data object:
                            </p>
                            <div class="doc-code-block">
                                <pre><code>{
  "event": "webhook.test",
  "timestamp": "2026-03-01T12:00:00+00:00",
  "data": {}
}</code></pre>
                            </div>
                        </section>

                        <!-- See Also -->
                        <section class="doc-section" id="see-also">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                                See Also
                            </h2>
                            <ul class="doc-list">
                                <li><x-link href="{{ route('marketing.docs.developer.api') }}">REST API Reference</x-link> - Full API documentation for managing schedules and events</li>
                                <li><x-link href="{{ route('marketing.docs.account_settings') }}">Account Settings</x-link> - Configure webhooks in your account</li>
                                <li><x-link href="{{ route('marketing.docs.tickets') }}">Selling Tickets</x-link> - Learn about the ticketing system that generates sale events</li>
                            </ul>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
