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

    <section class="py-16 sm:py-24 bg-white dark:bg-[#1e1e1e]">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Header -->
            <div class="mb-12">
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
                    <a href="{{ route('marketing.docs') }}" class="hover:text-gray-900 dark:hover:text-white">Docs</a>
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    <span>Developer</span>
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    <span class="text-gray-900 dark:text-white">Webhooks</span>
                </div>
                <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-4">Webhooks</h1>
                <p class="text-xl text-gray-600 dark:text-gray-300">Receive real-time HTTP POST notifications when events happen in your schedules.</p>
                <div class="mt-4 inline-flex items-center px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-medium">Pro plan required</div>
            </div>

            <!-- Overview -->
            <section class="doc-section" id="overview">
                <h2 class="doc-heading">Overview</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    Webhooks let you receive automatic POST notifications to your server when key events occur, such as ticket sales, event changes, or check-ins. Instead of polling the API, your application is notified in real time.
                </p>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    Each webhook delivery includes an HMAC-SHA256 signature so you can verify the payload came from Event Schedule. Delivery logs are available in your account settings for debugging.
                </p>
            </section>

            <!-- Setup -->
            <section class="doc-section" id="setup">
                <h2 class="doc-heading">Setup</h2>
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
                <h2 class="doc-heading">Event Types</h2>
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
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Payload Format -->
            <section class="doc-section" id="payload">
                <h2 class="doc-heading">Payload Format</h2>
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
                <h2 class="doc-heading">Request Headers</h2>
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
                <h2 class="doc-heading">Signature Verification</h2>
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
                <h2 class="doc-heading">Best Practices</h2>
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
                <h2 class="doc-heading">Testing</h2>
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
                <h2 class="doc-heading">See Also</h2>
                <ul class="doc-list">
                    <li><x-link href="{{ route('marketing.docs.developer.api') }}">REST API Reference</x-link> - Full API documentation for managing schedules and events</li>
                    <li><x-link href="{{ route('marketing.docs.account_settings') }}">Account Settings</x-link> - Configure webhooks in your account</li>
                    <li><x-link href="{{ route('marketing.docs.tickets') }}">Selling Tickets</x-link> - Learn about the ticketing system that generates sale events</li>
                </ul>
            </section>
        </div>
    </section>
</x-marketing-layout>
