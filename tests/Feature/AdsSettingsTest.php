<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\AdsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The operator-facing monetization card at /admin/settings.
 *
 * The card deliberately posts to its own endpoint. The two older cards share
 * admin.settings.update and therefore have to carry each other's values through as hidden
 * inputs - a pattern that is quadratic in the number of cards and fails silently. The
 * isolation tests below are the reason that decision was made, so they guard it.
 */
class AdsSettingsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['ads.enabled' => true, 'app.is_nexus' => false]);
    }

    private function adminActing(): User
    {
        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        return $admin;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'ads_adsense_enabled' => '1',
            'ads_adsense_client_id' => 'ca-pub-1234567890123456',
            'ads_adsense_slot_id' => '1234567890',
            'ads_native_enabled' => '1',
            'ads_native_priority' => '1',
            'ads_native_cpm' => '2.50',
            'ads_native_cpc' => '0.30',
        ], $overrides);
    }

    public function test_the_card_renders_when_the_env_gate_is_on(): void
    {
        $this->adminActing();

        $this->get(route('admin.settings'))
            ->assertOk()
            ->assertSee(__('messages.monetization_settings_title'));
    }

    public function test_the_card_is_hidden_when_the_env_gate_is_off(): void
    {
        // No point offering a switch that cannot do anything.
        config(['ads.enabled' => false]);
        $this->adminActing();

        $this->get(route('admin.settings'))
            ->assertOk()
            ->assertDontSee(__('messages.monetization_settings_title'));
    }

    public function test_settings_are_persisted(): void
    {
        $this->adminActing();

        $this->post(route('admin.settings.update_ads'), $this->validPayload())
            ->assertRedirect(route('admin.settings'));

        $this->assertTrue(AdsService::boolSetting('adsense_enabled'));
        $this->assertSame('ca-pub-1234567890123456', AdsService::setting('adsense_client_id'));
        $this->assertSame('1234567890', AdsService::setting('adsense_slot_id'));
        $this->assertTrue(AdsService::adSenseConfigured());
    }

    public function test_turning_a_toggle_off_stores_zero_not_null(): void
    {
        // A null read falls through to the config/env default, so storing null for "off"
        // would silently re-enable the feature on any install that set the env var.
        $this->adminActing();

        $this->post(route('admin.settings.update_ads'), $this->validPayload(['ads_adsense_enabled' => null]));

        $this->assertSame('0', Setting::get('ads_adsense_enabled'));

        config(['ads.adsense_enabled' => true]);
        $this->assertFalse(AdsService::boolSetting('adsense_enabled'));
    }

    public function test_saving_monetization_does_not_disturb_the_other_settings_cards(): void
    {
        $this->adminActing();

        Setting::set('custom_header_code', '<!-- keep me -->');
        Setting::set('federation_enabled', '1');
        Setting::set('federation_contact_email', 'ops@gmail.com');

        $this->post(route('admin.settings.update_ads'), $this->validPayload());

        $this->assertSame('<!-- keep me -->', Setting::get('custom_header_code'));
        $this->assertSame('1', Setting::get('federation_enabled'));
        $this->assertSame('ops@gmail.com', Setting::get('federation_contact_email'));
    }

    public function test_saving_another_card_does_not_disturb_monetization(): void
    {
        $this->adminActing();

        $this->post(route('admin.settings.update_ads'), $this->validPayload());

        $this->post(route('admin.settings.update'), [
            'custom_header_code' => '<!-- new -->',
            'custom_footer_code' => '',
        ]);

        $this->assertTrue(AdsService::boolSetting('adsense_enabled'));
        $this->assertSame('ca-pub-1234567890123456', AdsService::setting('adsense_client_id'));
    }

    public function test_publisher_and_slot_ids_are_validated(): void
    {
        // Both are interpolated into a <script src> URL, so they are validated rather than
        // trusted just because a super-admin typed them.
        $this->adminActing();

        $this->post(route('admin.settings.update_ads'), $this->validPayload([
            'ads_adsense_client_id' => 'ca-pub-1234"></script><script>alert(1)</script>',
        ]))->assertSessionHasErrors('ads_adsense_client_id');

        $this->post(route('admin.settings.update_ads'), $this->validPayload([
            'ads_adsense_slot_id' => '123abc',
        ]))->assertSessionHasErrors('ads_adsense_slot_id');
    }

    public function test_non_admins_cannot_change_monetization(): void
    {
        $this->actingAs($this->createOwner());

        $this->post(route('admin.settings.update_ads'), $this->validPayload());

        $this->assertNull(Setting::get('ads_adsense_client_id'));
    }

    public function test_the_endpoint_refuses_when_the_env_gate_is_off(): void
    {
        config(['ads.enabled' => false]);
        $this->adminActing();

        $this->post(route('admin.settings.update_ads'), $this->validPayload());

        $this->assertNull(Setting::get('ads_adsense_client_id'));
    }
}
