<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Markup checks for the signed-out fan-content forms (issue #108).
 *
 * These exist because the interesting failures here are rendering ones, not logic ones: an
 * anti-spam widget that lands in a hidden container never runs, and one that lands inside a
 * per-type @if disappears when that type is switched off. Neither shows up in a controller
 * test, and both were live bugs in the first cut of this feature.
 */
class GuestFanContentRenderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function enableTurnstile(): void
    {
        // Cloudflare's public always-passes test keys.
        config([
            'services.turnstile.site_key' => '1x00000000000000000000AA',
            'services.turnstile.secret_key' => '1x0000000000000000000000000000000AA',
        ]);
    }

    public function test_event_page_shows_guest_name_and_email_fields_when_signed_out(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['name' => 'Fan Content Night']);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringContainsString('name="guest_name"', $html);
        $this->assertStringContainsString('name="guest_email"', $html);
    }

    public function test_event_page_hides_guest_fields_when_the_schedule_requires_an_account(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $role->update(['fan_content_require_account' => true]);
        $event = $this->createEvent($role);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringNotContainsString('name="guest_name"', $html);
        $this->assertStringNotContainsString('id="fan-content-turnstile"', $html);
    }

    public function test_event_page_hides_guest_fields_from_signed_in_visitors(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $visitor = $this->createOwner();

        $html = $this->actingAs($visitor)
            ->get($this->guestEventUrl($role, $event))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('name="guest_name"', $html);
    }

    public function test_turnstile_widget_renders_once_and_below_the_button_row(): void
    {
        $this->enableTurnstile();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'id="fan-content-turnstile"'), 'exactly one shared widget per page');

        // The widget must come after the Add comment button, not be laid out as one of the
        // buttons in that flex row.
        $lastButton = strrpos($html, __('messages.add_comment'));
        $widget = strpos($html, 'id="fan-content-turnstile"');
        $this->assertNotFalse($lastButton);
        $this->assertNotFalse($widget);
        $this->assertGreaterThan($lastButton, $widget, 'widget must render after the action buttons');

        // Every guest form needs a token field for the shared widget to fill in.
        $this->assertStringContainsString('class="fan-content-turnstile-token"', $html);
    }

    public function test_turnstile_widget_still_renders_when_fan_videos_are_disabled(): void
    {
        // Regression: the include was briefly nested inside @if(isFanVideosEnabled()), so a
        // schedule with videos off rendered guest fields but no widget, and every guest
        // submission failed on an empty token.
        $this->enableTurnstile();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $role->update(['fan_videos_enabled' => false, 'fan_photos_enabled' => false]);
        $event = $this->createEvent($role);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringContainsString('name="guest_name"', $html);
        $this->assertStringContainsString('id="fan-content-turnstile"', $html);
    }

    public function test_photo_gallery_renders_the_widget_outside_the_collapsible_panel(): void
    {
        // Regression: inside x-show the container is display:none at load, and Turnstile does
        // not reliably auto-render there, so the token stayed empty and uploads always failed.
        $this->enableTurnstile();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $html = $this->get($event->getPhotoGalleryUrl($role->subdomain))->assertOk()->getContent();

        $widget = strpos($html, 'id="fan-content-turnstile"');
        $panel = strpos($html, 'x-show="showUpload"');

        $this->assertNotFalse($widget, 'gallery must render the shared widget');
        $this->assertNotFalse($panel);
        $this->assertLessThan($panel, $widget, 'widget must sit outside (before) the collapsible upload panel');
    }

    public function test_no_turnstile_markup_when_turnstile_is_not_configured(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        // Selfhost installs typically have no keys; the guest form must still work.
        $this->assertStringNotContainsString('id="fan-content-turnstile"', $html);
        $this->assertStringNotContainsString('class="fan-content-turnstile-token"', $html);
        $this->assertStringContainsString('name="guest_name"', $html);
    }
}
