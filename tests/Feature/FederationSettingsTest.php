<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Operator-side controls: the system-level switch, and the per-schedule toggle it gates.
 */
class FederationSettingsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Federation is an instance-side feature; the suite runs as the nexus.
        config(['app.is_nexus' => false]);
        Http::fake();
    }

    private function adminActing(): User
    {
        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        return $admin;
    }

    public function test_the_settings_page_shows_the_network_card_and_a_preview(): void
    {
        $admin = $this->adminActing();

        // The system switch has to be on before a schedule can opt in at all - the
        // model refuses the field otherwise - and the preview lists only what actually
        // opted in, never an undecided schedule.
        Setting::set('federation_enabled', '1');
        $role = $this->createRole($admin, 'venue', ['federation_enabled' => true]);
        $this->createEvent($role, ['name' => 'Preview Me', 'flyer_image_url' => 'f.jpg', 'creator_role_id' => $role->id]);

        $this->get(route('admin.settings'))
            ->assertOk()
            ->assertSee(__('messages.federation_settings_title'))
            // Enabling should never be a leap of faith.
            ->assertSee('Preview Me');
    }

    public function test_enabling_the_network_registers_the_install(): void
    {
        $this->adminActing();

        $this->post(route('admin.settings.update'), [
            // The marker the federation form carries, so the controller knows these
            // fields were submitted rather than merely absent.
            'federation_settings_submitted' => '1',
            'federation_enabled' => '1',
            'federation_contact_email' => 'ops@operator.test',
        ])->assertRedirect();

        $this->assertSame('1', Setting::get('federation_enabled'));
        $this->assertSame('ops@operator.test', Setting::get('federation_contact_email'));

        Http::assertSent(fn ($request) => str_contains($request->url(), '/api/federation/register'));
    }

    public function test_the_per_schedule_toggle_is_hidden_until_the_operator_opts_in(): void
    {
        $owner = $this->createOwner();
        $this->actingAs($owner);
        $role = $this->createRole($owner, 'venue');

        $this->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertDontSee(__('messages.federation_schedule_toggle'));

        Setting::set('federation_enabled', '1');

        $this->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertSee(__('messages.federation_schedule_toggle'));
    }

    /**
     * The control is hidden when the gate is closed, but RoleController fills from the
     * request in several places, so the field itself has to be refused too.
     */
    public function test_the_field_cannot_be_set_by_posting_it_while_the_gate_is_closed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $role->federation_enabled = false;
        $role->saveQuietly();

        // Gate closed: the change is refused even though the field is fillable.
        $role->refresh();
        $role->federation_enabled = true;
        $role->save();
        $this->assertFalse($role->fresh()->federation_enabled);

        // Gate open: the same change goes through.
        Setting::set('federation_enabled', '1');
        $role->refresh();
        $role->federation_enabled = true;
        $role->save();
        $this->assertTrue($role->fresh()->federation_enabled);
    }

    /**
     * Both cards on /admin/settings post to the same endpoint, and an unchecked toggle
     * looks identical to an absent field. Without a submitted-marker, saving the
     * header/footer card silently disabled federation and wiped the contact email -
     * and because federation_instance_id survives, the adoption banner would stay
     * suppressed and never invite the operator back.
     */
    public function test_saving_the_header_footer_card_does_not_disable_federation(): void
    {
        $this->adminActing();

        Setting::set('federation_enabled', '1');
        Setting::set('federation_contact_email', 'ops@operator.test');

        // The header/footer form carries no federation fields at all.
        $this->post(route('admin.settings.update'), [
            'custom_header_code' => '<!-- Google Tag Manager -->',
            'custom_footer_code' => '',
        ])->assertRedirect();

        $this->assertSame('1', Setting::get('federation_enabled'), 'Saving header/footer code turned federation off.');
        $this->assertSame('ops@operator.test', Setting::get('federation_contact_email'));
        $this->assertSame('<!-- Google Tag Manager -->', Setting::get('custom_header_code'));
    }

    /** And the federation form still owns its own fields. */
    public function test_the_federation_form_can_still_turn_it_off(): void
    {
        $this->adminActing();
        Setting::set('federation_enabled', '1');

        $this->post(route('admin.settings.update'), [
            'federation_settings_submitted' => '1',
            'federation_enabled' => '0',
            'custom_header_code' => '',
            'custom_footer_code' => '',
        ])->assertRedirect();

        $this->assertNull(Setting::get('federation_enabled'));
    }

    /**
     * A schedule created after the feature landed has not answered the question, and
     * "not answered" is a third state - not a yes, and importantly not a no either.
     */
    public function test_a_new_schedule_starts_undecided_and_is_not_shared(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->assertNull($role->fresh()->federation_enabled);
        $this->assertSame(1, app(\App\Services\FederationService::class)->undecidedScheduleCount());
    }

    /**
     * Saving an unrelated setting must not answer the question on the owner's behalf.
     *
     * The field used to be an x-toggle, whose hidden companion input posts "0" whether or not the
     * owner touched it - so editing a logo turned "never asked" into an explicit opt-out, which
     * vetoes the whole event and withdrew co-listed schedules' already-published listings.
     */
    public function test_an_unrelated_save_leaves_an_undecided_schedule_undecided(): void
    {
        Setting::set('federation_enabled', '1');

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->assertNull($role->fresh()->federation_enabled);

        // Checked while the schedule is still undecided, and before any save: the round-trips below
        // only exercise the controller, which already handled all three values - the bug was
        // entirely in the markup, so without this the toggle could be put back and the suite would
        // stay green.
        $html = $this->actingAs($owner)->get('/'.$role->subdomain.'/edit')->assertOk()->getContent();
        $this->assertStringNotContainsString(
            '<input type="hidden" name="federation_enabled"',
            $html,
            'The field posts a hidden companion again, so an untouched save writes a decision'
        );
        $this->assertStringContainsString('<option value="" SELECTED', $html);

        $save = fn (array $extra = []) => $this->actingAs($owner)->put(
            route('role.update', ['subdomain' => $role->subdomain]),
            array_merge([
                'name' => 'Renamed Venue',
                'timezone' => $role->timezone,
                'email' => $role->email,
                'new_subdomain' => $role->subdomain,
            ], $extra)
        )->assertRedirect();

        // The three-state control posts '' for "not decided", which arrives as null.
        $save(['federation_enabled' => '']);
        $this->assertNull($role->fresh()->federation_enabled);

        // And both real answers still round-trip.
        $save(['federation_enabled' => '1']);
        $this->assertTrue($role->fresh()->federation_enabled);

        $save(['federation_enabled' => '0']);
        $this->assertFalse($role->fresh()->federation_enabled);

        // The stored answer is what the control comes back showing.
        $this->assertStringContainsString(
            '<option value="0" SELECTED',
            $this->actingAs($owner)->get('/'.$role->subdomain.'/edit')->assertOk()->getContent()
        );
    }

    /**
     * The reason the column is nullable rather than a boolean defaulting to false.
     *
     * federatableQuery() lets any participating schedule veto an event, because a
     * listing carries every participant's name and the venue's address. If undecided
     * read as opted out, every unclaimed placeholder and every venue invented by
     * calendar sync would veto events that publish perfectly well.
     */
    public function test_an_undecided_schedule_does_not_veto_a_co_listed_event(): void
    {
        Setting::set('federation_enabled', '1');

        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $venue->federation_enabled = true;
        $venue->save();

        $event = $this->createEvent($venue, [
            'name' => 'Co-listed',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $venue->id,
        ]);

        // A second schedule on the same event that nobody has decided about.
        $talent = $this->createRole($owner, 'talent');
        $this->assertNull($talent->fresh()->federation_enabled);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $query = app(\App\Services\FederationService::class)->federatableQuery();
        $this->assertTrue($query->where('events.id', $event->id)->exists());

        // An explicit opt-out still vetoes it, unchanged.
        $talent->federation_enabled = false;
        $talent->save();

        $query = app(\App\Services\FederationService::class)->federatableQuery();
        $this->assertFalse($query->where('events.id', $event->id)->exists());
    }

    public function test_the_network_card_is_absent_on_the_nexus(): void
    {
        $this->adminActing();
        config(['app.is_nexus' => true]);

        $this->get(route('admin.settings'))
            ->assertOk()
            ->assertDontSee(__('messages.federation_settings_title'));
    }

    /**
     * ROLE_EXPORT_FIELDS is an explicit allowlist, so a new column silently vanishes
     * from a backup unless it is added. federated_at is instance-local sync state and
     * must NOT travel.
     */
    public function test_the_backup_allowlist_carries_the_schedule_opt_out(): void
    {
        $constant = new \ReflectionClassConstant(\App\Services\BackupService::class, 'ROLE_EXPORT_FIELDS');
        $fields = $constant->getValue();

        $this->assertContains('federation_enabled', $fields);
        // Sync state is instance-local and must never travel with a backup.
        $this->assertNotContains('federated_at', $fields);
    }

    public function test_a_failed_sync_is_surfaced_without_leaking_the_raw_response(): void
    {
        $this->adminActing();
        Setting::set('federation_enabled', '1');
        Setting::set('federation_last_error', 'unreachable');

        $this->get(route('admin.settings'))
            ->assertOk()
            ->assertSee(__('messages.federation_error_unreachable'));
    }
}
