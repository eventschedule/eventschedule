<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\Stay22Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The operator card at /admin/settings, and the schedule-level fields on the edit form.
 *
 * The card posts to its own endpoint rather than reusing updateAdsSettings(), which refuses
 * the request unless ADS_ENABLED && hosted && ! nexus. Those conditions do not apply to this
 * feature and would make the field unreachable on exactly the installs that want it, so the
 * isolation tests below guard that decision.
 */
class Stay22SettingsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        config(['stay22.enabled' => true, 'stay22.aid' => null, 'services.google.backend' => null]);
    }

    private function adminActing(): User
    {
        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        return $admin;
    }

    // ---------------------------------------------------------------- operator endpoint

    public function test_a_non_admin_cannot_change_the_operator_affiliate_id(): void
    {
        $this->actingAs($this->createOwner());

        $this->post(route('admin.settings.update_stay22'), ['stay22_aid' => 'sneaky']);

        Cache::flush();
        $this->assertNull(Setting::get('stay22_aid'));
    }

    public function test_an_admin_can_store_the_operator_affiliate_id(): void
    {
        $this->adminActing();

        $this->post(route('admin.settings.update_stay22'), ['stay22_aid' => 'operator-aid'])
            ->assertRedirect(route('admin.settings'));

        Cache::flush();
        $this->assertSame('operator-aid', Setting::get('stay22_aid'));
        $this->assertSame('operator-aid', Stay22Service::operatorAid());
    }

    public function test_the_endpoint_refuses_when_the_master_switch_is_off(): void
    {
        config(['stay22.enabled' => false]);
        $this->adminActing();

        $this->post(route('admin.settings.update_stay22'), ['stay22_aid' => 'operator-aid']);

        Cache::flush();
        $this->assertNull(Setting::get('stay22_aid'));
    }

    public function test_an_affiliate_id_containing_url_metacharacters_is_rejected(): void
    {
        $this->adminActing();

        $this->post(route('admin.settings.update_stay22'), ['stay22_aid' => 'abc&evil=1'])
            ->assertSessionHasErrors('stay22_aid');

        Cache::flush();
        $this->assertNull(Setting::get('stay22_aid'));
    }

    public function test_an_empty_affiliate_id_is_stored_as_null_not_an_empty_string(): void
    {
        $this->adminActing();
        Setting::set('stay22_aid', 'operator-aid');
        Cache::flush();

        $this->post(route('admin.settings.update_stay22'), ['stay22_aid' => '   ']);

        Cache::flush();
        $this->assertNull(Setting::get('stay22_aid'));
    }

    public function test_the_card_is_hidden_until_the_master_switch_is_on(): void
    {
        $this->adminActing();

        $this->get(route('admin.settings'))->assertOk()->assertSee('stay22_aid', false);

        config(['stay22.enabled' => false]);

        $this->get(route('admin.settings'))->assertOk()->assertDontSee('stay22_aid', false);
    }

    // ---------------------------------------------------------------- schedule fields

    public function test_the_engagement_tab_renders_only_when_the_master_switch_is_on(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $url = route('role.edit', ['subdomain' => $role->subdomain]);

        // Assert on the markup, not the bare id: HelpUtils' anchor map ships
        // 'engagement-tab-accommodation' into every admin page regardless of this gate.
        $this->actingAs($owner)->get($url)->assertOk()
            ->assertSee('id="engagement-tab-accommodation"', false)
            ->assertSee('data-tab="accommodation"', false)
            ->assertSee('name="stay22_enabled"', false)
            ->assertSee('name="stay22_aid"', false);

        config(['stay22.enabled' => false]);

        $this->actingAs($owner)->get($url)->assertOk()
            ->assertDontSee('id="engagement-tab-accommodation"', false)
            ->assertDontSee('data-tab="accommodation"', false)
            ->assertDontSee('name="stay22_enabled"', false)
            ->assertDontSee('name="stay22_aid"', false);
    }

    /**
     * The disclosure is the whole ethical basis for the operator fallback, so it must actually
     * appear once a schedule is live on someone else's affiliate ID.
     */
    public function test_the_fallback_warning_appears_when_the_schedule_has_no_own_id(): void
    {
        Setting::set('stay22_aid', 'operator-aid');
        Cache::flush();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['stay22_enabled' => true, 'stay22_aid' => null]);

        $warning = __('messages.stay22_no_own_aid_warning', ['operator' => marketing_domain()]);

        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertSee($warning, false);

        // ...and goes away once they connect their own account.
        $role->update(['stay22_aid' => 'owner-aid']);

        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertDontSee($warning, false);
    }

    public function test_a_schedule_owner_can_enable_the_map_and_set_their_own_id(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->actingAs($owner)
            ->put(route('role.update', ['subdomain' => $role->subdomain]), $this->rolePayload($role, [
                'stay22_enabled' => '1',
                'stay22_aid' => 'owner-aid',
            ]));

        $role->refresh();

        $this->assertTrue((bool) $role->stay22_enabled);
        $this->assertSame('owner-aid', $role->stay22_aid);
    }

    public function test_an_empty_schedule_affiliate_id_becomes_null(): void
    {
        // '' and null must not both be storable: resolveAid() reads null as "fall back to the
        // operator", so an empty string would compare differently everywhere that matters.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['stay22_aid' => 'owner-aid']);

        $this->actingAs($owner)
            ->put(route('role.update', ['subdomain' => $role->subdomain]), $this->rolePayload($role, [
                'stay22_enabled' => '1',
                'stay22_aid' => '',
            ]));

        $this->assertNull($role->refresh()->stay22_aid);
    }

    public function test_an_invalid_schedule_affiliate_id_is_rejected(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->actingAs($owner)
            ->put(route('role.update', ['subdomain' => $role->subdomain]), $this->rolePayload($role, [
                'stay22_enabled' => '1',
                'stay22_aid' => 'abc&lat=0',
            ]))
            ->assertSessionHasErrors('stay22_aid');

        $this->assertNull($role->refresh()->stay22_aid);
    }

    /**
     * RoleController::update() saves with fill($request->all()), not validated(), so without
     * the guard a hand-crafted POST could store these on an install that never opted in.
     */
    public function test_the_fields_cannot_be_set_while_the_master_switch_is_off(): void
    {
        config(['stay22.enabled' => false]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->actingAs($owner)
            ->put(route('role.update', ['subdomain' => $role->subdomain]), $this->rolePayload($role, [
                'stay22_enabled' => '1',
                'stay22_aid' => 'owner-aid',
            ]));

        $role->refresh();

        $this->assertFalse((bool) $role->stay22_enabled);
        $this->assertNull($role->stay22_aid);
    }

    /** Minimum fields RoleUpdateRequest needs, plus whatever the test is exercising. */
    private function rolePayload($role, array $overrides = []): array
    {
        return array_merge([
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'language_code' => 'en',
            // Required by RoleUpdateRequest; omitting it fails validation before the fields
            // under test are ever reached.
            'new_subdomain' => $role->subdomain,
        ], $overrides);
    }
}
