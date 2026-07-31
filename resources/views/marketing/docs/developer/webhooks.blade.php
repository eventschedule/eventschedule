<x-docs-page
    key="developer/webhooks"
    description="Receive real-time POST notifications for sales, events, and check-ins. HMAC-signed payloads, configurable event types, and delivery logs."
    lede="Receive real-time HTTP POST notifications when events happen in your schedules."
    article-description="Receive real-time POST notifications for sales, events, and check-ins via webhooks."
    plan="pro"
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#setup">Setup</x-doc-nav-link>
        <x-doc-nav-link href="#event-types">Event Types</x-doc-nav-link>
        <x-doc-nav-link href="#payload">Payload Format</x-doc-nav-link>
        <x-doc-nav-link href="#headers">Request Headers</x-doc-nav-link>
        <x-doc-nav-link href="#verification">Signature Verification</x-doc-nav-link>
        <x-doc-nav-link href="#best-practices">Best Practices</x-doc-nav-link>
        <x-doc-nav-link href="#testing">Testing</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section class="doc-section" id="overview">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Webhooks let you receive automatic POST notifications on your own server when something happens in your schedules: a ticket is sold, an event changes, a ticket is scanned at the door. Instead of polling the API, your application is notified as it happens.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Each delivery carries an HMAC-SHA256 signature so you can verify the payload really came from Event Schedule, and every attempt is written to a delivery log you can open from your settings.
        </p>

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Webhooks are a Pro feature</div>
            <p><x-doc-badge plan="pro" /> A webhook only fires for events that belong to a schedule on the <strong class="text-gray-900 dark:text-white">Pro</strong> plan or above. You can add and test a webhook on any account, but if none of your schedules is Pro, nothing will ever be delivered. A <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install counts as Enterprise, so webhooks are available there with no plan restriction.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Webhooks belong to your <strong class="text-gray-900 dark:text-white">account</strong>, not to an individual schedule. One endpoint receives activity from every schedule you own, and the schedule is identifiable from the payload. Add more than one endpoint if you want to route different event types to different services.
        </p>

        <h3 class="doc-subheading">How a delivery works</h3>
        <div class="doc-table-wrap mb-6">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Behaviour</th>
                        <th>What to expect</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Method and body</td>
                        <td>A single <code class="doc-inline-code">POST</code> with a JSON body and the headers listed below.</td>
                    </tr>
                    <tr>
                        <td>Timeout</td>
                        <td>5 seconds. A slower endpoint is recorded as a failed delivery.</td>
                    </tr>
                    <tr>
                        <td>Retries</td>
                        <td>Up to 3 attempts, waiting roughly 30 seconds and then 60 seconds between them.</td>
                    </tr>
                    <tr>
                        <td>What is retried</td>
                        <td>Timeouts, connection errors and <code class="doc-inline-code">5xx</code> responses. Any <code class="doc-inline-code">2xx</code> counts as delivered, and a <code class="doc-inline-code">4xx</code> is treated as a permanent rejection and is not retried.</td>
                    </tr>
                    <tr>
                        <td>Redirects</td>
                        <td>Not followed. Return your <code class="doc-inline-code">2xx</code> at the exact URL you registered.</td>
                    </tr>
                    <tr>
                        <td>Allowed endpoints</td>
                        <td>Public <code class="doc-inline-code">http</code> or <code class="doc-inline-code">https</code> URLs only. Loopback, private, reserved and cloud metadata addresses are rejected, both when you save the webhook and again at send time.</td>
                    </tr>
                    <tr>
                        <td>Delivery log</td>
                        <td>Every attempt is logged with its status, duration and the first part of your response. The list shows the 20 most recent, and entries are pruned after 30 days.</td>
                    </tr>
                </tbody>
            </table>
        </div>
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
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Settings</strong> in the admin panel and choose <strong class="text-gray-900 dark:text-white">Webhooks</strong>, then scroll to the <strong class="text-gray-900 dark:text-white">Add Webhook</strong> form.</li>
            <li>Enter the <strong class="text-gray-900 dark:text-white">Webhook URL</strong>. It has to be a publicly reachable address, so <code class="doc-inline-code">localhost</code> and private network addresses are refused with "This URL is not allowed". Use HTTPS: the payload contains buyer names, email addresses and ticket links.</li>
            <li>Optionally add a <strong class="text-gray-900 dark:text-white">Description</strong>, a label for your own reference that appears above the URL in the list.</li>
            <li>Under <strong class="text-gray-900 dark:text-white">Event types</strong>, switch off anything you do not want. Every type is on by default, and leaving them all on subscribes the endpoint to everything, including any type added later.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Add Webhook</strong>. The signing secret, a 64-character hex string, is shown once with a copy button. Store it before you leave the page: it cannot be displayed again.</li>
            <li>Send a test ping with the <strong class="text-gray-900 dark:text-white">Test</strong> button on the saved webhook and confirm your endpoint answers with a 2xx status. The result is reported as "Test webhook sent successfully (HTTP 200)" or as a failure with the status it did get.</li>
        </ol>

        <h3 class="doc-subheading">Managing a webhook</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Each saved webhook shows its description, URL, the event types it subscribes to (or an <strong class="text-gray-900 dark:text-white">All events</strong> badge) and when it was last triggered. The icon buttons on the right of the row do the following.
        </p>
        <ul class="doc-list">
            <li><strong class="text-gray-900 dark:text-white">Enable / Disable</strong> - the tick icon pauses or resumes the webhook. A disabled webhook is dimmed in the list and receives nothing, but keeps its secret and its delivery history.</li>
            <li><strong class="text-gray-900 dark:text-white">Test</strong> - the lightning icon sends the test payload described under <a href="#testing" class="doc-link">Testing</a>.</li>
            <li><strong class="text-gray-900 dark:text-white">Edit</strong> - the pencil icon opens an inline form for the URL, description and event types. <strong class="text-gray-900 dark:text-white">Regenerate secret</strong> sits at the bottom of that form; it issues a new secret, shows it once, and immediately invalidates the old one, so update your endpoint in the same sitting.</li>
            <li><strong class="text-gray-900 dark:text-white">Delete</strong> - the trash icon removes the webhook and its delivery log after a confirmation.</li>
            <li><strong class="text-gray-900 dark:text-white">View recent deliveries</strong> - the link under the row expands the last 20 attempts with the event type, response status, duration and time.</li>
        </ul>
    </section>

    <!-- Event Types -->
    <section class="doc-section" id="event-types">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            Event Types
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            These are the twelve types you can subscribe to. They are the same list, in the same order, as the switches on the Add Webhook form.
        </p>
        <div class="doc-table-wrap mb-6">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Fires when</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td class="font-mono text-sm">sale.created</td><td>An order is created: a checkout, an RSVP, an appointment booking, or a sale created through the API. At this point the sale is normally still unpaid.</td></tr>
                    <tr><td class="font-mono text-sm">sale.paid</td><td>A sale is confirmed as paid, by Stripe, by Invoice Ninja, by being marked paid on the Sales page, or immediately after <code class="doc-inline-code">sale.created</code> for a free order or RSVP.</td></tr>
                    <tr><td class="font-mono text-sm">sale.refunded</td><td>A sale is refunded from the Sales page or the API.</td></tr>
                    <tr><td class="font-mono text-sm">sale.cancelled</td><td>A sale is cancelled, either by the owner or by the ticket holder from their ticket page.</td></tr>
                    <tr><td class="font-mono text-sm">event.created</td><td>An event is published. Publishing an existing draft counts as a creation.</td></tr>
                    <tr><td class="font-mono text-sm">event.updated</td><td>A published event is saved with changes, including an appointment being rescheduled.</td></tr>
                    <tr><td class="font-mono text-sm">event.deleted</td><td>A published event is deleted. The payload is captured before the row is removed.</td></tr>
                    <tr><td class="font-mono text-sm">event.cancelled</td><td>An event is cancelled rather than deleted.</td></tr>
                    <tr><td class="font-mono text-sm">ticket.scanned</td><td>A ticket or pass QR code is scanned and accepted at check-in.</td></tr>
                    <tr><td class="font-mono text-sm">ticket.booked</td><td>A pass holder reserves a place on a specific date in advance.</td></tr>
                    <tr><td class="font-mono text-sm">ticket.booking_cancelled</td><td>A pass holder releases a place they had reserved.</td></tr>
                    <tr><td class="font-mono text-sm">feedback.submitted</td><td>An attendee submits a rating, and optionally a comment, for an event they attended.</td></tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Drafts never fire an event webhook</div>
            <p>Saving or deleting a draft event sends nothing. The first delivery for a draft is the <code class="doc-inline-code">event.created</code> you get when it is published.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Group orders send one delivery per ticket holder</div>
            <p>When one buyer checks out for several named guests, each row in the order gets its own <code class="doc-inline-code">sale.*</code> delivery. The primary row carries the totals for the whole group; the guest rows report <code class="doc-inline-code">payment_amount</code> as <code class="doc-inline-code">0</code> so you do not count the money twice. Use <code class="doc-inline-code">is_primary</code> and <code class="doc-inline-code">group_id</code> in the payload to tell them apart.</p>
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
        <p class="text-gray-600 dark:text-gray-300 mb-4">Every payload uses the same three-key envelope: the type in <code class="doc-inline-code">event</code>, an ISO 8601 <code class="doc-inline-code">timestamp</code>, and the record itself in <code class="doc-inline-code">data</code>. Abbreviated example of a <code class="doc-inline-code">sale.paid</code> delivery:</p>
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
        <p class="text-gray-600 dark:text-gray-300 mt-4 mb-4">
            For <code class="doc-inline-code">sale.*</code> and <code class="doc-inline-code">event.*</code> the <code class="doc-inline-code">data</code> object is the same record the <a href="{{ route('marketing.docs.developer.api') }}#list-sales" class="doc-link">Sales API</a> and <a href="{{ route('marketing.docs.developer.api') }}#list-events" class="doc-link">Events API</a> return, so one parser can handle both. The real object carries more than the sample above: a sale also includes <code class="doc-inline-code">subdomain</code>, <code class="doc-inline-code">phone</code>, <code class="doc-inline-code">event_date</code>, <code class="doc-inline-code">payment_method</code>, <code class="doc-inline-code">transaction_reference</code>, discount and gift-card totals, <code class="doc-inline-code">total_quantity</code>, <code class="doc-inline-code">group_id</code>, <code class="doc-inline-code">is_primary</code> and timestamps, and each ticket row carries <code class="doc-inline-code">is_addon</code>, <code class="doc-inline-code">is_pass</code> and, for a pass, its usage counters.
        </p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Sale payloads contain the ticket secret</div>
            <p>Unlike an API response, a <code class="doc-inline-code">sale.*</code> webhook always includes the sale's <code class="doc-inline-code">secret</code>, the token that opens the ticket page and its QR code. Treat the whole payload as sensitive: use HTTPS, and do not log it or forward it somewhere public.</p>
        </div>

        <h3 class="doc-subheading">Types with extra fields</h3>
        <ul class="doc-list">
            <li><code class="doc-inline-code">ticket.scanned</code> from a pass adds <code class="doc-inline-code">scanned_event_id</code> and <code class="doc-inline-code">scanned_event_date</code>, so you can tell which occurrence the pass was used on.</li>
            <li><code class="doc-inline-code">ticket.booked</code> adds <code class="doc-inline-code">booked_event_id</code> and <code class="doc-inline-code">booked_event_date</code>.</li>
            <li><code class="doc-inline-code">ticket.booking_cancelled</code> adds the same two fields plus <code class="doc-inline-code">forfeited</code>, which is <code class="doc-inline-code">true</code> when the release happened after the cancellation cutoff and the visit was used up.</li>
            <li><code class="doc-inline-code">feedback.submitted</code> is the one type that does not follow the API shape. Its <code class="doc-inline-code">data</code> holds <code class="doc-inline-code">event_id</code>, <code class="doc-inline-code">event_name</code>, <code class="doc-inline-code">event_date</code>, <code class="doc-inline-code">attendee_name</code>, <code class="doc-inline-code">attendee_email</code>, <code class="doc-inline-code">rating</code> and <code class="doc-inline-code">comment</code>, and it has no <code class="doc-inline-code">id</code>.</li>
        </ul>
    </section>

    <!-- Headers -->
    <section class="doc-section" id="headers">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
            </svg>
            Request Headers
        </h2>
        <div class="doc-table-wrap mb-6">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Header</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td class="font-mono text-sm">X-Webhook-Signature</td><td>HMAC-SHA256 signature: <code class="doc-inline-code">sha256=&lt;hex&gt;</code></td></tr>
                    <tr><td class="font-mono text-sm">X-Webhook-Event</td><td>The event type (e.g. <code class="doc-inline-code">sale.paid</code>), matching <code class="doc-inline-code">event</code> in the body</td></tr>
                    <tr><td class="font-mono text-sm">X-Webhook-Timestamp</td><td>ISO 8601 timestamp of when this attempt was sent. On a retry it is newer than the <code class="doc-inline-code">timestamp</code> in the body, which is fixed when the payload is built.</td></tr>
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
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The signature covers the request body exactly as sent and nothing else, so hash the raw bytes before any JSON parsing or re-encoding. Compare with a constant-time function, never with <code class="doc-inline-code">==</code>.
        </p>

        <h3 class="doc-subheading">PHP</h3>
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

        <h3 class="doc-subheading">Node.js</h3>
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

        <h3 class="doc-subheading">Python</h3>
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
            <li><strong class="text-gray-900 dark:text-white">Respond quickly.</strong> Return a 2xx status within 5 seconds. Queue the real work and acknowledge receipt first, or a slow database write will be recorded as a failed delivery and retried.</li>
            <li><strong class="text-gray-900 dark:text-white">Verify signatures.</strong> Always validate the <code class="doc-inline-code">X-Webhook-Signature</code> header before processing any payload, and reject anything that does not match.</li>
            <li><strong class="text-gray-900 dark:text-white">Expect duplicates.</strong> A timeout on your side still counts as a failure, so a delivery you did process can arrive again. Use <code class="doc-inline-code">data.id</code> together with <code class="doc-inline-code">event</code> as an idempotency key, and fall back to the event and attendee for <code class="doc-inline-code">feedback.submitted</code>, which has no id.</li>
            <li><strong class="text-gray-900 dark:text-white">Answer at the registered URL.</strong> Redirects are not followed, so a 301 from <code class="doc-inline-code">http</code> to <code class="doc-inline-code">https</code> or from a bare domain to <code class="doc-inline-code">www</code> is recorded as a failure. Register the final URL.</li>
            <li><strong class="text-gray-900 dark:text-white">Use HTTPS.</strong> Payloads carry buyer names, email addresses and ticket secrets, so they should never cross the network in the clear.</li>
            <li><strong class="text-gray-900 dark:text-white">Return a 4xx only when you mean it.</strong> A 4xx is treated as a permanent rejection and stops the retries; use a 5xx when you want the delivery attempted again.</li>
            <li><strong class="text-gray-900 dark:text-white">Monitor deliveries.</strong> Open <strong class="text-gray-900 dark:text-white">View recent deliveries</strong> in your webhook settings to debug failures. The response body you return is stored with the log, so a descriptive error message there pays for itself.</li>
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
            Use the <strong class="text-gray-900 dark:text-white">Test</strong> button in your webhook settings to send a test payload. The test event uses the type <code class="doc-inline-code">webhook.test</code> with an empty data object:
        </p>
        <div class="doc-code-block">
            <pre><code>{
"event": "webhook.test",
"timestamp": "2026-03-01T12:00:00+00:00",
"data": {}
}</code></pre>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mt-4">
            The test is signed and sent exactly like a real delivery, with the same headers and the same 5 second timeout, so it verifies your signature check as well as your URL. It is not retried, it ignores the event types you subscribed to, and it works whatever plan your schedules are on, which makes it the quickest way to prove the endpoint itself before you wait for real activity. The result is written to the delivery log alongside everything else.
        </p>
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
            <li><a href="{{ route('marketing.docs.developer.api') }}" class="doc-link">REST API Reference</a> - The same records over HTTP, for anything you need to pull rather than be pushed</li>
            <li><a href="{{ route('marketing.docs.account_settings') }}#webhooks" class="doc-link">Account Settings</a> - Where webhooks, API keys and connected services are configured</li>
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - The ticketing and check-in features behind the sale and scan events</li>
        </ul>
    </section>
</x-docs-page>
