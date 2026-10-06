<?php

namespace Tests\Feature;

use App\Http\Controllers\RoleController;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule save changes what its form showed, and nothing else.
 *
 * RoleController::update() fills the schedule from the whole request, and it is open to every
 * member who may edit the schedule, not only its owner. So a column that is fillable for the code
 * that sets it by hand could be posted by anybody who could save: the schedule's type, the state
 * of its custom domain's verification, the credentials of the calendar server it syncs to. And a
 * list the page did not send read as a list that had been emptied, which deleted every
 * sub-schedule on a save that never showed them.
 */
class ScheduleSaveProtectionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    private function save(User $user, Role $role, array $fields = []): TestResponse
    {
        return $this->actingAs($user)
            ->from(route('role.edit', ['subdomain' => $role->subdomain]))
            ->put(route('role.update', ['subdomain' => $role->subdomain]), array_merge([
                'name' => $role->name,
                'email' => $role->email,
                'timezone' => $role->timezone,
                'new_subdomain' => $role->subdomain,
            ], $fields));
    }

    /** A member who may edit the schedule and does not own it. */
    private function editorOf(Role $role): User
    {
        $editor = $this->createOwner();
        $role->users()->attach($editor->id, ['level' => 'admin']);
        $this->assertTrue($editor->fresh()->isEditor($role->subdomain), 'sanity check: the member may save the schedule');

        return $editor->fresh();
    }

    public function test_a_save_cannot_change_what_kind_of_schedule_it_is(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');

        $this->save($owner, $talent, ['type' => 'curator'])->assertRedirect();

        $this->assertSame('talent', $talent->fresh()->type);
    }

    public function test_the_state_of_a_custom_domain_is_not_taken_from_the_form(): void
    {
        $owner = $this->createOwner();
        $enterprise = $this->createRole($owner, 'talent');
        $free = $this->createFreeRole($this->createOwner(), 'talent');

        foreach ([$enterprise, $free] as $role) {
            $this->save($role->user, $role, [
                'custom_domain_host' => 'somebody-else.example.org',
                'custom_domain_status' => 'active',
                'custom_domain_error' => 'forged',
            ])->assertRedirect();

            $fresh = $role->fresh();
            $this->assertNull($fresh->custom_domain_host, 'the host is derived from the domain, never posted');
            $this->assertNull($fresh->custom_domain_status, 'active means the domain was verified, which a form cannot claim');
            $this->assertNull($fresh->custom_domain_error);
        }
    }

    public function test_a_real_custom_domain_still_sets_its_own_host(): void
    {
        config(['services.digitalocean.app_hostname' => null]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->save($owner, $role, ['custom_domain' => 'https://tickets.bluenote.example.org', 'custom_domain_mode' => 'redirect'])->assertRedirect();

        $this->assertSame('tickets.bluenote.example.org', $role->fresh()->custom_domain_host, 'sanity check: the guard does not stop the real path');
    }

    public function test_counters_and_calendar_credentials_are_not_taken_from_the_form(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        Role::where('id', $role->id)->update(['last_notified_request_count' => 3, 'last_notified_poll_option_count' => 5]);

        $this->save($this->editorOf($role), $role, [
            'last_notified_request_count' => 999,
            'last_notified_poll_option_count' => 999,
            'caldav_settings' => ['server_url' => 'https://calendar.example.org', 'username' => 'x', 'password' => 'y', 'calendar_url' => 'https://calendar.example.org/c'],
            'import_config' => ['urls' => ['http://169.254.169.254/']],
        ])->assertRedirect();

        $fresh = $role->fresh();
        $this->assertSame(3, (int) $fresh->last_notified_request_count);
        $this->assertSame(5, (int) $fresh->last_notified_poll_option_count);
        $this->assertNull($fresh->getRawOriginal('caldav_settings'), 'the server a schedule syncs its events to is set by the owner, through a connection that is tested');
        $this->assertNotContains('http://169.254.169.254/', $fresh->import_config['urls'] ?? [], 'import addresses go through the list that checks them');
    }

    public function test_only_the_owner_changes_which_way_caldav_syncs(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        Role::where('id', $role->id)->update(['caldav_sync_direction' => 'to']);

        $this->save($this->editorOf($role), $role, ['caldav_sync_direction' => 'both'])->assertRedirect();
        $this->assertSame('to', $role->fresh()->caldav_sync_direction, 'the connection is the owner\'s, and so is its direction');

        $this->save($owner, $role, ['caldav_sync_direction' => 'both'])->assertRedirect();
        $this->assertSame('both', $role->fresh()->caldav_sync_direction);
    }

    /** Beside the direction radios on the page, and as much the owner's as they are. */
    public function test_only_the_owner_changes_what_a_deleted_calendar_event_does(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        Role::where('id', $role->id)->update(['calendar_delete_action' => 'ignore']);

        $this->save($this->editorOf($role), $role, ['calendar_delete_action' => 'delete'])->assertRedirect();
        $this->assertSame('ignore', $role->fresh()->calendar_delete_action, 'deleting events here when they are deleted there is not a member\'s call');

        $this->save($owner, $role, ['calendar_delete_action' => 'delete'])->assertRedirect();
        $this->assertSame('delete', $role->fresh()->calendar_delete_action);

        $this->save($owner, $role, ['calendar_delete_action' => 'everything'])->assertSessionHasErrors('calendar_delete_action');
        $this->assertSame('delete', $role->fresh()->calendar_delete_action);
    }

    /** A curator schedule has no "default curators" on its page, so its save cannot set any. */
    public function test_a_curator_schedule_takes_no_default_curators_from_a_save(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $other = $this->createCurator($this->createOwner());

        $this->save($owner, $curator, ['default_curator_ids' => [$other->id]])->assertRedirect();

        $this->assertNull($curator->fresh()->default_curator_ids);
    }

    public function test_a_plan_without_custom_fields_or_labels_cannot_post_them(): void
    {
        $owner = $this->createOwner();
        $free = $this->createFreeRole($owner, 'talent');
        $this->assertFalse($free->fresh()->isPro());

        $this->save($owner, $free, [
            'event_custom_fields' => ['abc' => ['name' => 'Dress code', 'type' => 'string']],
            'custom_labels' => ['event' => ['value' => 'Gig']],
        ])->assertRedirect();

        $this->assertNull($free->fresh()->event_custom_fields);
        $this->assertNull($free->fresh()->custom_labels);
    }

    public function test_an_outlook_calendar_survives_a_save_made_while_its_list_was_loading(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $pivot = RoleUser::where('role_id', $role->id)->where('user_id', $owner->id)->first();
        $pivot->update(['microsoft_calendar_id' => 'CAL-123']);

        // The select holds an empty value until its options arrive, and after a failed load.
        $this->save($owner, $role, ['microsoft_integration_submitted' => 1, 'microsoft_calendar_id' => ''])->assertRedirect();

        $this->assertSame('CAL-123', RoleUser::find($pivot->id)->microsoft_calendar_id);
    }

    public function test_only_the_owner_changes_the_outlook_calendar(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        Role::where('id', $role->id)->update(['microsoft_sync_direction' => 'to', 'microsoft_create_teams_meetings' => true]);
        $pivot = RoleUser::where('role_id', $role->id)->where('user_id', $owner->id)->first();
        $pivot->update(['microsoft_calendar_id' => 'CAL-123']);

        $this->save($this->editorOf($role), $role, [
            'microsoft_integration_submitted' => 1,
            'microsoft_calendar_id' => 'SOMEONE-ELSES',
            'microsoft_sync_direction' => 'both',
        ])->assertRedirect();

        $fresh = $role->fresh();
        $this->assertSame('CAL-123', RoleUser::find($pivot->id)->microsoft_calendar_id);
        $this->assertSame('to', $fresh->microsoft_sync_direction);
        $this->assertTrue((bool) $fresh->microsoft_create_teams_meetings, 'a switch the member was never shown is not switched off');
    }

    public function test_a_refused_save_gives_back_what_was_typed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $taken = $this->createRole($owner, 'talent');

        $response = $this->save($owner, $role, ['name' => 'A name typed before the refusal', 'new_subdomain' => $taken->subdomain]);

        $response->assertSessionHasErrors('new_subdomain');
        $this->assertSame('A name typed before the refusal', session()->getOldInput('name'));
        $this->assertNotSame('A name typed before the refusal', $role->fresh()->name, 'sanity check: the save was refused');
    }

    public function test_sub_schedules_survive_a_save_that_did_not_show_them(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->createGroup($role, ['name' => 'Main stage']);
        $this->createGroup($role, ['name' => 'Late shows']);

        $this->save($owner, $role)->assertRedirect();

        $this->assertSame(2, $role->groups()->count());
    }

    public function test_a_sub_schedule_taken_off_the_list_is_still_deleted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $kept = $this->createGroup($role, ['name' => 'Main stage']);
        $this->createGroup($role, ['name' => 'Late shows']);

        $this->save($owner, $role, ['groups_submitted' => 1, 'groups' => [$kept->id => ['name' => 'Main stage']]])->assertRedirect();
        $this->assertSame(['Main stage'], $role->groups()->pluck('name')->all());

        // And the last one: the list is on the page and empty.
        $this->save($owner, $role, ['groups_submitted' => 1])->assertRedirect();
        $this->assertSame(0, $role->groups()->count());
    }

    /** The picker lists the curators the person saving belongs to, so their save decides only those. */
    public function test_default_curators_another_member_chose_survive_a_save(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $ownersCurator = $this->createCurator($owner);
        Role::where('id', $role->id)->update(['default_curator_ids' => json_encode([$ownersCurator->id])]);

        $editor = $this->editorOf($role);
        $editorsCurator = $this->createCurator($editor);

        // The member's page offered only their own curator, and they ticked it.
        $this->save($editor, $role, ['default_curator_ids' => ['', $editorsCurator->id]])->assertRedirect();
        $this->assertEqualsCanonicalizing([$ownersCurator->id, $editorsCurator->id], $role->fresh()->default_curator_ids);

        // Unticking it takes off theirs, and leaves the owner's.
        $this->save($editor, $role, ['default_curator_ids' => ['']])->assertRedirect();
        $this->assertSame([$ownersCurator->id], array_values($role->fresh()->default_curator_ids));

        // The owner sees theirs and can take it off.
        $this->save($owner, $role, ['default_curator_ids' => ['']])->assertRedirect();
        $this->assertNull($role->fresh()->default_curator_ids);
    }

    /**
     * Keeping what another member chose must not keep what NOBODY on the schedule can manage any
     * more (the curator was unfollowed, or stopped taking requests): the picker no longer lists
     * it for anyone, so nobody could untick it, and every new event still went to it.
     */
    public function test_a_default_curator_no_member_can_manage_any_more_drops_out(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $stale = $this->createCurator($this->createOwner());
        $ownersCurator = $this->createCurator($owner);
        Role::where('id', $role->id)->update(['default_curator_ids' => json_encode([$stale->id, $ownersCurator->id])]);
        $this->assertNotContains($stale->id, $owner->fresh()->allCurators()->pluck('id')->all(), 'sanity check: the owner cannot manage it');

        // The owner saves with their own curator still ticked.
        $this->save($owner, $role, ['default_curator_ids' => ['', $ownersCurator->id]])->assertRedirect();

        $this->assertSame([$ownersCurator->id], array_values($role->fresh()->default_curator_ids));

        // And an editor's save drops it too, while keeping the owner's.
        Role::where('id', $role->id)->update(['default_curator_ids' => json_encode([$stale->id, $ownersCurator->id])]);
        $this->save($this->editorOf($role), $role, ['default_curator_ids' => ['']])->assertRedirect();
        $this->assertSame([$ownersCurator->id], array_values($role->fresh()->default_curator_ids));
    }

    /** Who a schedule's event graphics are mailed to, and when, is set on the graphics page, which checks it. */
    public function test_graphic_settings_are_not_taken_from_the_form(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $stored = ['enabled' => false, 'recipient_emails' => 'owner@gmail.com'];
        $role->graphic_settings = $stored;
        $role->save();

        $this->save($this->editorOf($role), $role, [
            'name' => 'Saved all the same',
            'graphic_settings' => ['enabled' => true, 'recipient_emails' => str_repeat('someone@gmail.com,', 200), 'frequency' => 'hourly'],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('Saved all the same', $role->fresh()->name, 'sanity check: the save went through');

        // The raw column: the accessor fills in defaults around whatever is stored.
        $this->assertEquals($stored, json_decode($role->fresh()->getRawOriginal('graphic_settings'), true));
    }

    /** On a paid plan too: the lists are rebuilt by their own blocks, which run when the list was on the page. */
    public function test_custom_fields_and_labels_are_only_changed_by_a_save_that_showed_them(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->assertTrue($role->isPro());
        $fields = ['abc12345' => ['name' => 'Dress code', 'type' => 'string', 'index' => 1]];
        $labels = ['event' => ['value' => 'Gig']];
        Role::where('id', $role->id)->update(['event_custom_fields' => json_encode($fields), 'custom_labels' => json_encode($labels)]);

        // Posted by hand, without the marker each list posts beside itself. Valid values, so
        // that nothing but the missing marker stands between them and the columns.
        $this->save($owner, $role, [
            'name' => 'Saved all the same',
            'event_custom_fields' => ['zzz99999' => ['name' => 'Raw', 'type' => 'string']],
            'custom_labels' => ['event' => ['value' => 'Show']],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('Saved all the same', $role->fresh()->name, 'sanity check: the save went through');
        $this->assertEquals($fields, $role->fresh()->event_custom_fields);
        $this->assertEquals($labels, $role->fresh()->custom_labels);

        // The list on the page still decides: emptied there, it is emptied.
        $this->save($owner, $role, ['custom_labels_submitted' => 1])->assertRedirect();
        $this->assertNull($role->fresh()->custom_labels);
        $this->assertEquals($fields, $role->fresh()->event_custom_fields, 'and the other list, not shown, is left alone');
    }

    public function test_the_owner_cannot_post_an_outlook_direction_without_the_outlook_controls(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        Role::where('id', $role->id)->update(['microsoft_sync_direction' => 'to']);

        // No marker: the Outlook controls were not on the page (not connected, or another form).
        $this->save($owner, $role, ['microsoft_sync_direction' => 'both'])->assertRedirect();
        $this->assertSame('to', $role->fresh()->microsoft_sync_direction);

        $this->save($owner, $role, ['microsoft_integration_submitted' => 1, 'microsoft_sync_direction' => 'both'])->assertRedirect();
        $this->assertSame('both', $role->fresh()->microsoft_sync_direction, 'sanity check: with the controls it is theirs to set');
    }

    public function test_the_last_import_address_and_city_can_be_taken_off(): void
    {
        config(['app.hosted' => false]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $role->import_config = ['urls' => ['https://example.org/events'], 'cities' => ['chicago']];
        $role->save();

        // A save that never showed the lists leaves them alone.
        $this->save($owner, $role)->assertRedirect();
        $this->assertSame(['https://example.org/events'], $role->fresh()->import_config['urls']);

        // Both lists on the page, with every row removed.
        $this->save($owner, $role, ['import_lists_submitted' => 1])->assertRedirect();
        $this->assertSame([], $role->fresh()->import_config['urls']);
        $this->assertSame([], $role->fresh()->import_config['cities']);
    }

    public function test_a_refused_import_address_gives_back_what_was_typed(): void
    {
        config(['app.hosted' => false]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $response = $this->save($owner, $role, [
            'name' => 'A name typed before the refusal',
            'import_lists_submitted' => 1,
            'import_urls' => ['http://127.0.0.1/internal'],
        ]);

        $response->assertSessionHasErrors('import_urls');
        $this->assertSame('A name typed before the refusal', session()->getOldInput('name'));
        $this->assertNotSame('A name typed before the refusal', $role->fresh()->name, 'sanity check: the save was refused');
    }

    public function test_the_last_gift_card_amount_can_be_taken_off(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        Role::where('id', $role->id)->update(['gift_card_amounts' => json_encode([25, 50])]);

        // A save that never showed the list leaves it alone.
        $this->save($owner, $role)->assertRedirect();
        $this->assertEquals([25, 50], $role->fresh()->gift_card_amounts);

        // The list on the page, with every amount removed.
        $this->save($owner, $role, ['gift_card_amounts_submitted' => 1])->assertRedirect();
        $this->assertEmpty($role->fresh()->gift_card_amounts);
    }

    public function test_a_second_language_survives_a_save_that_did_not_show_its_switch(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        Role::where('id', $role->id)->update(['language_code' => 'he', 'translation_language_code' => 'en']);

        $this->save($owner, $role, ['language_code' => 'he'])->assertRedirect();
        $this->assertSame('en', $role->fresh()->translation_language_code);

        // The switch, shown and turned off, still means "no second language".
        $this->save($owner, $role, ['language_code' => 'he', 'translation_enabled' => 0])->assertRedirect();
        $this->assertSame('he', $role->fresh()->translation_language_code);
    }

    public function test_a_gradient_survives_a_save_that_did_not_show_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        Role::where('id', $role->id)->update(['background_colors' => '#111111, #222222']);

        $this->save($owner, $role)->assertRedirect();
        $this->assertSame('#111111, #222222', $role->fresh()->background_colors);

        // "Custom" on the page: the select is empty and the two colours say what it is.
        $this->save($owner, $role, ['background_colors' => '', 'custom_color1' => '#aaaaaa', 'custom_color2' => '#bbbbbb'])->assertRedirect();
        $this->assertSame('#aaaaaa, #bbbbbb', $role->fresh()->background_colors);
    }

    public function test_a_new_schedule_takes_none_of_them_either(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner);
        $role = $this->submitNewScheduleForm('talent', [
            'custom_domain_host' => 'somebody-else.example.org',
            'custom_domain_status' => 'active',
            'last_notified_request_count' => 999,
            'caldav_settings' => ['server_url' => 'https://calendar.example.org', 'username' => 'x', 'password' => 'y', 'calendar_url' => 'https://calendar.example.org/c'],
        ]);

        $this->assertSame('talent', $role->type, 'sanity check: the kind of schedule IS chosen when it is made');
        $this->assertNull($role->custom_domain_host);
        $this->assertNull($role->custom_domain_status);
        $this->assertSame(0, (int) $role->last_notified_request_count);
        $this->assertNull($role->getRawOriginal('caldav_settings'));
    }

    public function test_a_new_schedule_takes_no_sponsor_list_from_the_form(): void
    {
        // No form posts sponsor_logos: the list is built from the logos uploaded. Stored as posted,
        // a file name in it is deleted as an orphan the next time the schedule is saved or removed,
        // and it can be the name of another schedule's logo.
        $owner = $this->createOwner();

        $this->actingAs($owner);
        $role = $this->submitNewScheduleForm('talent', [
            'sponsor_logos' => json_encode([['logo' => 'sponsor_somebody_elses.png', 'name' => 'Not mine']]),
        ]);

        $this->assertEmpty($role->sponsor_logos, 'a new schedule starts with no sponsors of its own');
    }

    public function test_a_new_schedule_sends_its_events_only_to_curators_its_maker_was_offered(): void
    {
        // The picker lists the curators of the person making the schedule. A posted id it never
        // offered used to be stored, and every new event then went to that curator as a request.
        $owner = $this->createOwner();
        $mine = $this->createCurator($owner);
        $strangers = $this->createCurator($this->createOwner());

        $this->actingAs($owner);
        $role = $this->submitNewScheduleForm('talent', [
            'default_curator_ids' => ['', (string) $mine->id, (string) $strangers->id],
        ]);

        $this->assertSame([$mine->id], array_map('intval', $role->default_curator_ids ?? []), 'the one that was offered, and only it');

        // And a curator schedule takes none, as on a later save.
        $curator = $this->submitNewScheduleForm('curator', [
            'name' => 'A second curator',
            'default_curator_ids' => [(string) $mine->id],
        ]);
        $this->assertEmpty($curator->default_curator_ids);
    }

    public function test_a_new_schedule_made_without_colours_stores_no_empty_pair(): void
    {
        // The short form a first schedule is made on shows no background at all, and ", " was
        // stored as its gradient.
        $owner = $this->createOwner();

        $this->actingAs($owner);
        $payload = ['name' => 'Bare', 'type' => 'talent', 'email' => 'bare.schedule@gmail.com', 'timezone' => 'America/New_York', 'language_code' => 'en'];
        $this->post(route('role.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $role = Role::where('user_id', $owner->id)->latest('id')->firstOrFail();

        $this->assertNotSame(', ', $role->background_colors);

        // A form that did show them still stores the pair it chose.
        $this->post(route('role.store'), $payload + ['name' => 'Coloured', 'background_colors' => '', 'custom_color1' => '#112233', 'custom_color2' => '#445566'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('#112233, #445566', Role::where('user_id', $owner->id)->latest('id')->firstOrFail()->background_colors);
    }

    public function test_lists_rebuilt_only_when_shown_are_not_taken_raw_when_they_were_not(): void
    {
        // Both columns are fillable, and each has a block that tidies what was posted when its
        // marker says the list was on the page. Without the marker the block does not run, and
        // the posted array used to be stored exactly as it came.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $role->approved_subdomains = ['kept-one'];
        $role->event_categories = [['id' => 100, 'name' => 'Kept']];
        $role->save();

        $this->save($owner, $role, [
            'approved_subdomains' => ['  posted raw  ', ''],
            'event_categories' => [['id' => 100, 'name' => 'Posted raw', 'disabled' => 1]],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $role->refresh();
        $this->assertSame(['kept-one'], $role->approved_subdomains);
        $this->assertSame([['id' => 100, 'name' => 'Kept']], $role->event_categories);
    }

    /**
     * The list is only useful while it names real fillable columns: a column that stops being
     * fillable no longer needs it, and one misspelt here protects nothing.
     */
    public function test_every_server_owned_field_is_one_the_request_could_otherwise_fill(): void
    {
        $fillable = (new Role)->getFillable();

        foreach (RoleController::SERVER_OWNED_FIELDS as $field) {
            $this->assertContains($field, $fillable, "{$field} is not fillable, so it does not belong in the list");
        }
        $this->assertContains('custom_domain_status', RoleController::SERVER_OWNED_FIELDS);
        $this->assertContains('caldav_settings', RoleController::SERVER_OWNED_FIELDS);
    }

    /**
     * "Some other member can still manage it" means a member who can open this form. Someone with
     * view access cannot, so a curator only they can reach is on nobody's page either.
     */
    public function test_a_default_curator_only_a_viewer_can_reach_drops_out(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $viewer = $this->createOwner();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);
        $viewersCurator = $this->createCurator($viewer);
        $ownersCurator = $this->createCurator($owner);
        Role::where('id', $role->id)->update(['default_curator_ids' => json_encode([$viewersCurator->id, $ownersCurator->id])]);
        $this->assertContains($viewersCurator->id, $viewer->fresh()->allCurators()->pluck('id')->all(), 'sanity check: the viewer can reach it');

        $this->save($owner, $role, ['default_curator_ids' => ['', $ownersCurator->id]])->assertRedirect();

        $this->assertSame([$ownersCurator->id], array_values($role->fresh()->default_curator_ids));
    }

    /**
     * A refused gift card amount is a message per row, and the list under the field printed a list
     * as text: the schedule form came back as a 500. Reachable by typing a perfectly ordinary
     * amount in a currency that counts in the hundred thousands.
     */
    public function test_a_refused_gift_card_amount_comes_back_with_its_message(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $edit = route('role.edit', ['subdomain' => $role->subdomain]);

        $this->actingAs($owner)->from($edit)
            ->put(route('role.update', ['subdomain' => $role->subdomain]), [
                'name' => $role->name, 'email' => $role->email, 'timezone' => 'America/New_York', 'language_code' => 'en',
                'gift_cards_enabled' => 1, 'gift_card_amounts_submitted' => 1, 'gift_card_amounts' => ['25', '100000'],
            ])
            ->assertRedirect($edit)
            ->assertSessionHasErrors('gift_card_amounts.1');

        $html = $this->actingAs($owner)->get($edit)->assertOk()->getContent();

        $this->assertSame(1, preg_match('/<input type="number" name="gift_card_amounts\[\]" value="100000"/', $html), 'what was typed is still in its field');
        $this->assertStringContainsString('99999', strip_tags(substr($html, strpos($html, 'id="gift-card-amounts-items"'), 6000)), 'and the message that says why is beside it');
    }
}
