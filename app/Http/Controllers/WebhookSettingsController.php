<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\AuditService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookSettingsController extends Controller
{
    public function store(Request $request)
    {
        if (is_demo_mode()) {
            return redirect()->to(route('profile.edit').'#section-webhooks')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $request->validate([
            'url' => 'required|url|max:2048',
            'description' => 'nullable|string|max:255',
            'event_types' => 'nullable|array',
            'event_types.*' => 'string|in:'.implode(',', Webhook::EVENT_TYPES),
        ]);

        $url = $request->url;

        // Before the DNS check: what it says is the more useful answer.
        if (self::isOwnIncomingWebhook($url)) {
            return redirect()->to(route('profile.edit').'#section-webhooks')
                ->withInput()
                ->withErrors(['url' => __('messages.webhook_url_is_ours')]);
        }

        if (UrlUtils::validatedTarget($url) === null) {
            // On the field, with what was typed: it was a toast over an emptied form.
            return redirect()->to(route('profile.edit').'#section-webhooks')
                ->withInput()
                ->withErrors(['url' => __('messages.webhook_url_not_allowed')]);
        }

        $secret = bin2hex(random_bytes(32));

        $eventTypes = $request->event_types;
        if (empty($eventTypes) || count($eventTypes) === count(Webhook::EVENT_TYPES)) {
            $eventTypes = null;
        }

        $webhook = auth()->user()->webhooks()->create([
            'url' => $url,
            'secret' => $secret,
            'description' => $request->description,
            'event_types' => $eventTypes,
        ]);

        AuditService::log(AuditService::WEBHOOK_CREATE, auth()->id(), 'Webhook', $webhook->id, null, null, $url);

        return redirect()->to(route('profile.edit').'#section-webhooks')
            ->with('message', __('messages.webhook_created'))
            ->with('show_new_webhook_secret', true)
            ->with('new_webhook_secret', $secret);
    }

    public function update(Request $request, string $webhookHash)
    {
        if (is_demo_mode()) {
            return redirect()->to(route('profile.edit').'#section-webhooks')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $webhook = Webhook::findOrFail(UrlUtils::decodeIdOrFail($webhookHash));

        if ($webhook->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'url' => 'required|url|max:2048',
            'description' => 'nullable|string|max:255',
            'event_types' => 'nullable|array',
            'event_types.*' => 'string|in:'.implode(',', Webhook::EVENT_TYPES),
        ]);

        $url = $request->url;

        // Before the DNS check: what it says is the more useful answer.
        if (self::isOwnIncomingWebhook($url)) {
            return redirect()->to(route('profile.edit').'#section-webhooks')
                ->withInput()
                ->withErrors(['url' => __('messages.webhook_url_is_ours')]);
        }

        if (UrlUtils::validatedTarget($url) === null) {
            return redirect()->to(route('profile.edit').'#section-webhooks')
                ->withInput()
                ->withErrors(['url' => __('messages.webhook_url_not_allowed')]);
        }

        $eventTypes = $request->event_types;
        if (empty($eventTypes) || count($eventTypes) === count(Webhook::EVENT_TYPES)) {
            $eventTypes = null;
        }

        $webhook->update([
            'url' => $url,
            'description' => $request->description,
            'event_types' => $eventTypes,
        ]);

        AuditService::log(AuditService::WEBHOOK_UPDATE, auth()->id(), 'Webhook', $webhook->id, null, null, $url);

        return redirect()->to(route('profile.edit').'#section-webhooks')
            ->with('message', __('messages.webhook_updated'));
    }

    public function destroy(string $webhookHash)
    {
        if (is_demo_mode()) {
            return redirect()->to(route('profile.edit').'#section-webhooks')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $webhook = Webhook::findOrFail(UrlUtils::decodeIdOrFail($webhookHash));

        if ($webhook->user_id !== auth()->id()) {
            abort(403);
        }

        $webhookId = $webhook->id;
        $webhookUrl = $webhook->url;
        $webhook->delete();

        AuditService::log(AuditService::WEBHOOK_DELETE, auth()->id(), 'Webhook', $webhookId, null, null, $webhookUrl);

        return redirect()->to(route('profile.edit').'#section-webhooks')
            ->with('message', __('messages.webhook_deleted'));
    }

    public function toggle(string $webhookHash)
    {
        if (is_demo_mode()) {
            return redirect()->to(route('profile.edit').'#section-webhooks')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $webhook = Webhook::findOrFail(UrlUtils::decodeIdOrFail($webhookHash));

        if ($webhook->user_id !== auth()->id()) {
            abort(403);
        }

        $webhook->update(['is_active' => ! $webhook->is_active]);

        AuditService::log(AuditService::WEBHOOK_TOGGLE, auth()->id(), 'Webhook', $webhook->id,
            null, ['is_active' => $webhook->is_active], $webhook->url);

        return redirect()->to(route('profile.edit').'#section-webhooks')
            ->with('message', $webhook->is_active ? __('messages.webhook_enabled') : __('messages.webhook_disabled'));
    }

    public function regenerateSecret(string $webhookHash)
    {
        if (is_demo_mode()) {
            return redirect()->to(route('profile.edit').'#section-webhooks')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $webhook = Webhook::findOrFail(UrlUtils::decodeIdOrFail($webhookHash));

        if ($webhook->user_id !== auth()->id()) {
            abort(403);
        }

        $secret = bin2hex(random_bytes(32));
        $webhook->update(['secret' => $secret]);

        AuditService::log(AuditService::WEBHOOK_REGENERATE_SECRET, auth()->id(), 'Webhook', $webhook->id, null, null, $webhook->url);

        return redirect()->to(route('profile.edit').'#section-webhooks')
            ->with('message', __('messages.webhook_secret_regenerated'))
            ->with('show_new_webhook_secret', true)
            ->with('new_webhook_secret', $secret);
    }

    public function test(string $webhookHash)
    {
        if (is_demo_mode()) {
            return redirect()->to(route('profile.edit').'#section-webhooks')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $webhook = Webhook::findOrFail(UrlUtils::decodeIdOrFail($webhookHash));

        if ($webhook->user_id !== auth()->id()) {
            abort(403);
        }

        // A webhook saved before this check: say what it is rather than reporting the 400 the
        // app's own Stripe endpoint gives anything Stripe did not sign.
        if (self::isOwnIncomingWebhook($webhook->url)) {
            return redirect()->to(route('profile.edit').'#section-webhooks')
                ->with('error', __('messages.webhook_url_is_ours'));
        }

        // Validate + pin the target right before sending (TOCTOU-safe against DNS
        // rebinding); the same vetted IP is used for the request below.
        $curl = UrlUtils::safePinnedCurlOptions($webhook->url);
        if ($curl === null) {
            return redirect()->to(route('profile.edit').'#section-webhooks')
                ->with('error', __('messages.webhook_url_not_allowed'));
        }

        $payload = [
            'event' => 'webhook.test',
            'timestamp' => now()->toIso8601String(),
            'data' => new \stdClass,
        ];

        $jsonBody = json_encode($payload);
        $signature = hash_hmac('sha256', $jsonBody, $webhook->secret);

        $startTime = microtime(true);
        $responseBody = null;

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => 'sha256='.$signature,
                    'X-Webhook-Event' => 'webhook.test',
                    'X-Webhook-Timestamp' => now()->toIso8601String(),
                    'User-Agent' => 'EventSchedule-Webhook/1.0',
                ])
                ->withBody($jsonBody, 'application/json')
                ->withOptions([
                    'allow_redirects' => false,
                    'curl' => $curl,
                ])
                ->post($webhook->url);

            $status = $response->status();
            $success = $response->successful();
            $responseBody = mb_substr($response->body(), 0, 10000);
        } catch (\Exception $e) {
            $status = null;
            $success = false;
        }

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'event_type' => 'webhook.test',
            'payload' => $payload,
            'response_status' => $status,
            'response_body' => $responseBody,
            'success' => $success,
            'duration_ms' => $durationMs,
            'created_at' => now(),
        ]);

        if ($success) {
            return redirect()->to(route('profile.edit').'#section-webhooks')
                ->with('message', __('messages.webhook_test_success', ['status' => $status]));
        }

        // Name who answered: "Test webhook failed (HTTP 405)" read as the app failing, when it
        // was the receiving server saying no.
        $host = (string) parse_url((string) $webhook->url, PHP_URL_HOST);
        $failure = match (true) {
            $status === null => __('messages.webhook_test_failed', ['status' => 'timeout']),
            $status >= 300 && $status < 400 => __('messages.webhook_test_redirected', ['host' => $host, 'status' => $status]),
            default => __('messages.webhook_test_failed_at', ['host' => $host, 'status' => $status]),
        };

        return redirect()->to(route('profile.edit').'#section-webhooks')->with('error', $failure);
    }

    /**
     * Whether $url is one of this app's own incoming webhooks: where Stripe, a payment gateway or
     * WhatsApp sends ITS events here. A webhook on this page sends Event Schedule's events out to
     * another system, so one pointed at those can never work: the Stripe endpoint answers 400 to
     * anything Stripe did not sign, while Stripe's own calls go through, which reads as "the Test
     * button is broken" (support, 2026-11).
     */
    public static function isOwnIncomingWebhook(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $ours = array_filter([
            strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST)),
            strtolower((string) request()->getHost()),
        ]);

        if ($host === '' || ! in_array(preg_replace('/^www\./', '', $host), array_map(fn ($h) => preg_replace('/^www\./', '', $h), $ours), true)) {
            return false;
        }

        try {
            $route = app('router')->getRoutes()->match(Request::create($url, 'POST'));
        } catch (\Throwable) {
            return false;
        }

        return str_contains((string) $route->getName(), 'webhook');
    }

    public function deliveries(string $webhookHash)
    {
        $webhook = Webhook::findOrFail(UrlUtils::decodeIdOrFail($webhookHash));

        if ($webhook->user_id !== auth()->id()) {
            abort(403);
        }

        $deliveries = $webhook->deliveries()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return response()->json($deliveries);
    }
}
