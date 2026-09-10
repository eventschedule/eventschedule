<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Notifications tab: what a save stores, and which toggles render greyed out.
 *
 * <x-toggle> disables its hidden "0" along with the checkbox, so a greyed-out toggle posts nothing
 * at all. The save used to write all six keys anyway, and (bool) null is false, so every save of the
 * schedule stored each greyed-out preference as OFF - installment_due included, which defaults ON
 * because a failed installment is money that did not arrive. Rows already saved that way cannot be
 * told apart from a real opt-out; what this pins is that no new ones are written.
 */
class NotificationToggleSaveTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const SMTP = [
        'host' => 'smtp.test', 'username' => 'u', 'password' => 'p',
        'port' => 587, 'from_address' => 'sched@gmail.com', 'from_name' => 'Sched',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Greyed-out toggles are a hosted question (selfhost has no Email Settings tab), and
        // app.hosted comes from the .env, so pin it. Push starts unconfigured; tests opt in.
        config([
            'app.hosted' => true,
            'services.onesignal.app_id' => null,
            'services.onesignal.rest_api_key' => null,
        ]);
    }

    private function storePreferences(Role $role, User $user, ?string $json): void
    {
        $role->users()->updateExistingPivot($user->id, ['notification_settings' => $json]);
    }

    private function storedPreferences(Role $role, User $user): array
    {
        $raw = $role->users()->where('user_id', $user->id)->first()->pivot->notification_settings;

        return json_decode($raw ?? '{}', true);
    }

    private function saveSchedule(User $owner, Role $role, array $fields): void
    {
        $this->actingAs($owner)
            ->put(route('role.update', ['subdomain' => $role->subdomain]), array_merge([
                'name' => $role->name,
                'email' => $role->email,
                'timezone' => $role->timezone,
                'language_code' => 'en',
                // Required by RoleUpdateRequest; without it validation fails before the save.
                'new_subdomain' => $role->subdomain,
            ], $fields))
            ->assertSessionHasNoErrors();
    }

    public function test_a_save_changes_only_the_toggles_it_carries(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->storePreferences($role, $owner, json_encode([
            'new_sale' => true,
            'new_request' => false,
            'installment_due' => true,
        ]));

        // The shape of a save from a schedule without email settings: the greyed-out toggles post
        // nothing, the live ones post their 0 or 1.
        $this->saveSchedule($owner, $role, [
            'notification_new_request' => '1',
            'notification_new_fan_content' => '0',
        ]);

        $stored = $this->storedPreferences($role, $owner);

        $this->assertTrue($stored['new_request'], 'a posted 1 is stored');
        $this->assertFalse($stored['new_fan_content'], 'a posted 0 is stored');
        $this->assertTrue($stored['new_sale'], 'a toggle that posted nothing keeps its stored value');
        $this->assertTrue($stored['installment_due'], 'installment_due keeps its stored value');
        $this->assertArrayNotHasKey('new_feedback', $stored, 'a key never stored stays absent, so its default still applies');
        $this->assertArrayNotHasKey('new_poll_option', $stored);
    }

    public function test_an_unreadable_stored_value_is_started_over_rather_than_merged(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        // Valid JSON, so the column takes it, but json_decode() hands back null rather than an array.
        $this->storePreferences($role, $owner, 'null');

        $this->saveSchedule($owner, $role, ['notification_new_sale' => '1']);

        $this->assertSame(['new_sale' => true], $this->storedPreferences($role, $owner));
    }

    /** @return array<string, bool> toggle key => whether its checkbox renders disabled */
    private function greyedOut(User $owner, Role $role): array
    {
        $html = $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new \DOMXPath($dom);

        $states = [];
        foreach (['new_sale', 'new_feedback', 'new_poll_option', 'installment_due'] as $key) {
            $boxes = $xpath->query('//input[@type="checkbox"][@name="notification_'.$key.'"]');
            $this->assertSame(1, $boxes->length, "notification_{$key} renders exactly once");
            $states[$key] = $boxes->item(0)->hasAttribute('disabled');
        }

        return $states;
    }

    public function test_without_email_settings_or_push_only_installments_stay_usable(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['plan_type' => 'pro']);
        $this->assertFalse($role->hasEmailSettings());

        $this->assertSame([
            'new_sale' => true,
            'new_feedback' => true,
            'new_poll_option' => true,
            // The digest falls back to the platform mailer, so it is never greyed out.
            'installment_due' => false,
        ], $this->greyedOut($owner, $role));
    }

    public function test_push_keeps_the_sale_and_feedback_toggles_usable_on_pro(): void
    {
        config(['services.onesignal.app_id' => 'test-app-id', 'services.onesignal.rest_api_key' => 'test-rest-key']);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['plan_type' => 'pro']);
        $this->assertTrue($role->isPro());

        $this->assertSame([
            // Both push before they look for a mail transport.
            'new_sale' => false,
            'new_feedback' => false,
            // NotifyPollOptionChanges skips a hosted schedule without email settings before it pushes.
            'new_poll_option' => true,
            'installment_due' => false,
        ], $this->greyedOut($owner, $role));
    }

    public function test_push_does_not_unlock_them_below_pro(): void
    {
        config(['services.onesignal.app_id' => 'test-app-id', 'services.onesignal.rest_api_key' => 'test-rest-key']);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['plan_type' => 'free']);
        $this->assertFalse($role->isPro(), 'push is a Pro channel, so a free schedule has none');

        $states = $this->greyedOut($owner, $role);

        $this->assertTrue($states['new_sale']);
        $this->assertTrue($states['new_feedback']);
        $this->assertFalse($states['installment_due']);
    }

    public function test_with_email_settings_nothing_is_greyed_out(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['plan_type' => 'pro']);
        $role->email_settings = self::SMTP;
        $role->save();

        $this->assertSame([
            'new_sale' => false,
            'new_feedback' => false,
            'new_poll_option' => false,
            'installment_due' => false,
        ], $this->greyedOut($owner, $role->fresh()));
    }
}
