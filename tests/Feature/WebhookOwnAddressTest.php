<?php

namespace Tests\Feature;

use App\Http\Controllers\WebhookSettingsController;
use App\Models\Webhook;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A webhook on the settings page sends Event Schedule's events OUT. A selfhoster pointed one at the
 * app's own Stripe endpoint, where Stripe sends its events IN: Test then failed with the 400 that
 * endpoint gives anything Stripe did not sign, while Stripe's real calls went through, and the
 * Test button looked broken (support, 2026-11). Such an address is refused and explained.
 */
class WebhookOwnAddressTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_the_apps_own_incoming_webhooks_are_recognised(): void
    {
        foreach (['/stripe/webhook', '/stripe/subscription-webhook', '/payments/stripe/webhook', '/api/whatsapp/webhook'] as $path) {
            $this->assertTrue(WebhookSettingsController::isOwnIncomingWebhook(url($path)), $path);
        }

        $host = parse_url(config('app.url'), PHP_URL_HOST);
        $this->assertTrue(WebhookSettingsController::isOwnIncomingWebhook("https://www.{$host}/stripe/webhook"), 'www. is the same site');

        // Another site's own /stripe/webhook, and a page of ours that is not a webhook.
        $this->assertFalse(WebhookSettingsController::isOwnIncomingWebhook('https://hooks.example.com/stripe/webhook'));
        $this->assertFalse(WebhookSettingsController::isOwnIncomingWebhook(url('/pricing')));
        $this->assertFalse(WebhookSettingsController::isOwnIncomingWebhook('not a url'));
    }

    public function test_saving_one_is_refused_with_what_it_is(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner)->post(route('webhooks.store'), ['url' => url('/stripe/webhook'), 'description' => 'Stripe'])
            ->assertSessionHasErrors(['url' => __('messages.webhook_url_is_ours')]);

        $this->assertSame(0, Webhook::count());
    }

    public function test_one_saved_before_the_check_is_explained_by_test_instead_of_sent(): void
    {
        $owner = $this->createOwner();
        $webhook = $owner->webhooks()->create(['url' => url('/stripe/webhook'), 'secret' => str_repeat('a', 64)]);

        $this->actingAs($owner)->post(route('webhooks.test', ['webhookHash' => UrlUtils::encodeId($webhook->id)]))
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.webhook_url_is_ours'));

        $this->assertSame(0, $webhook->deliveries()->count(), 'nothing was sent');
    }
}
